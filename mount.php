<?php
namespace Also;

Class Mount {
    public $ext = 'mount';
    public $variables = [];
    public $viewPath = __DIR__.'/views';
    public $bind = false;

    function __construct($viewPath) {
        if(isset($viewPath)) $this->viewPath = $viewPath;
    }

    public function mount($route,$variables = [],$bind = false) {
        // $variables = $this->getGlobals($variables);
        $this->variables = $variables;
        
        if(isset($bind)) $this->bind = $bind;
        $string = $this->getFile($route);
        if(strpos($string,'@extends') !== false) $string = $this->extends($string);
        if(strpos($string,'@include') !== false) $string = $this->include($string);
        if(strpos($string,'@foreach') !== false) $string = $this->getFors($string,'@foreach','@endforeach');
        if(strpos($string,'@for') !== false) $string = $this->getFors($string,'@for','@endfor');
        if(strpos($string,'@if') !== false) $string = $this->getIfs($string);
        if(strpos($string,'@iferror') !== false) $string = $this->ifError($string);
        if(strpos($string,'{{') !== false) $string = $this->out($string);
            // if(string.includes('@iferror')) string = this.ifError(string,variables)
        foreach ($variables as $key => $value) {
            if(gettype($value) == 'object') $variables[$key] = (array)$value;
        }
        extract($variables);
        $string = $this->vars($string);
            // if(this.bind) string = this.bindVar(string)
            
        return eval('?>'.$string);
    }

    private function out($string) {
        $scripts = explode('{{',$string);
        foreach ($scripts as $key => $script) {
            if(strpos($script,'}}') !== false) {
                $script = explode('}}',$script)[0];
                $result = "<?php echo ".$script."; ?>";
                $string = str_replace('{{'.$script.'}}',$result,$string);
            }
        }
        return $string;
    }

    private function getGlobals($vars) {
        $exceptions = ['_COOKIE','_FILES','argv','phpPath','path','GLOBALS','_REQUEST','_SERVER','_SESSION','__composer_autoload_files'];
        foreach ($GLOBALS as $key => $value) {
            if(!in_array($key,$exceptions)) {
                // echo gettype($value).'   '.$key.'   ';
                $vars[$key] = $value;
            }
        }
        return $vars;
    }

    private function getFile($route) {
        $route = str_replace('.','/',$route);
        $route = $this->viewPath.'/'.$route.'.'.$this->ext;

        $content = @file_get_contents($route);
        if($content === FALSE) return '';
        else return $content;
    }

    private function extends($string) {
        $pattern = '/\\@extends\\s*?\\(.*\\)/';
        preg_match($pattern, $string,$matches);
        $layout = str_replace('@extends(','',$matches[0]);
        $layout = str_replace(')','',$layout);
        $layout = $this->getFile($layout);
        if($layout == null) return $string;
        $pattern = '/\\@section\\s*?\\(.*\\)(.|\\n)*?\\@endsection/';
        preg_match_all($pattern,$string,$sections);
        foreach ($sections[0] as $section) {
            preg_match('/\\@section\\s*?\\(.*\\)/',$section,$sectionStart);
            $sectionStart = $sectionStart[0];
            $sectionName = str_replace('@section(','',$sectionStart);
            $sectionName = str_replace(')','',$sectionName);
            $sectionName = trim($sectionName);
            $content = str_replace($sectionStart,'',$section);
            $content = str_replace('@endsection','',$content);
            $pattern = "/\\@mount\\s*?\\(\\s*?$sectionName\\s*?\\)/";
            $layout = preg_replace($pattern,$content,$layout);
        }
        $pattern = '/@mount\\s*?\\(\\s*?.*\\s*?\\)/';
        while(preg_match($pattern,$layout)) {
            $layout = preg_replace($pattern,'',$layout);
        }
        return $layout;
    }

    private function include($string) {
        $pattern = '/\\@include\\s*?\\(.*\\)/';
        preg_match_all($pattern,$string,$includes);
        foreach ($includes[0] as $include) {
            $route = str_replace('@include(','',$include);
            $route = str_replace(')','',$route);
            $content = $this->getFile($route);
            if($content == null) return $string;
            $string = str_replace($include,$content,$string);
        }
        return $string;
    }

    private function getFors($string,$start,$end) {
        $string = $this->replaceContent($string,$start);
        $string = $this->replaceEnd($string,$end);
        return $string;
    }
    
    private function getIfs($string) {
        $string = $this->replaceContent($string,'@if');
        $string = $this->replaceContent($string,'@elseif');
        $string = $this->replaceEnd($string,'@else',':');
        $string = $this->replaceEnd($string,'@endif');
        return $string;
    }

    private function replaceContent($string,$start) {
        $pattern = '/'.$start.'\\s*?\\(.*\\)/';
        preg_match_all($pattern,$string,$sections);
        foreach ($sections[0] as $section) {
            $expr = str_replace('@','',$section);
            $string = str_replace($section,"<?php $expr : ?>",$string);
        }
        return $string;
    }

    private function replaceEnd($string,$end,$endsWith = ';') {
        $pattern = '/'.$end.'/';
        preg_match_all($pattern,$string,$sections);
        foreach ($sections[0] as $section) {
            $expr = str_replace('@','',$section);
            $string = str_replace($section,"<?php ".$expr.$endsWith." ?>",$string);
        }
        return $string;
    }

    private function ifError($string) {
        // $pattern = '/@iferror\\s*?\\(.*\\)/';
        // preg_match_all($pattern,$string,$sections);
        // foreach ($sections[0] as $section) {
        //     $array = explode('(',$section);
        //     // pre($array);
        // }

        return $string;
    }

    public function vars($string) {
        $pattern = '/\\@\\w*(\\[.*?\\])*/';
        preg_match_all($pattern,$string,$sections);
        foreach ($sections[0] as $section) {
            $expr = str_replace('@','$',$section);
            preg_match_all("/#$section/",$string,$matches);
            if(count($matches[0]) == 0) {
                $replace = "<?php 
                if(isset($expr)) echo $expr;
                ?>";
                $string = str_replace($section,$replace,$string);
            } else {
                $string = str_replace('#'.$section,$section,$string);
            }
        }
        return $string;
    }

    private function toFile($string) {
        $file = fopen("test.php", "w");
        fwrite($file,$string);
        fclose($file);
    }

}
?>