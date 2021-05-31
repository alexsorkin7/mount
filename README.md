# mount
mount php code with shortcuts


## About

Mount is templeting engine for node. It mounts code and variables inside html (or any other) code on server side. 

## Define mount

```php
$viewPath = __DIR__.'/views';
$mount = new Mount($viewPath);

// the line below, will look for views/auth/login.mount file
$mount->mount('auth.login',['user'=>'Some']);

```

Syntax:
```php
new Mount($viewPath)->mount($mountPath,$variablesArray);

```

## Layout @extends, @mount, @section

Build layouts with sections. On layout file, define sections with ``@mount(sectionName)``

Inside file you want to mount in layout, use:

```php
@extends(fileName)
@section(sectionName) content @endsection
```

Example:

```php
<!-- Layout file - index.mount -->
<header>@mount(header)</header>
<body>
    @mount(content)
</body>
```

```php
<!-- File to mount in layout -->
@extends(index)
@section(title)content to mount @endsection

@section(content)
<div>Content to mount </div>
@endsection
```

## Partials @include
You can include every file in enother just by define @include(folder.file)

For example, you have some.mount inside partials folder and you want to use it as content inside index.mount. Here is the code inside index.mount:

Example: the file will include viewsFolder/partials/some.mount

```php
@include(partials.some)
```

That`s all!


## Conditions @if .. @else

You can use @if,@elseif and @else to define the condition for appearing the text below of condition.

You can use only variables you defined in a mount object, or new created variables with @for and @foreach ( will be explained further).

**The if statement must be finished with @endif**

Here is the example of using.

```php
$mount->mount('test',['name'=>'Alex']);
```

```php
<!-- test.mount -->
@if(name == 'Alex')
    <div>Hello @name, how are you</div>
@elseif (name == 'Robert')
    <div>Hello @name how are you?</div>
@elseif (name == 'Mary')
    <div>Hello @name you are 25</div>
@else
    <div>I dont know who you are!</div>
@endif
```

## forEach cycles @foreach

You can use foreach for iterating arrays.

You can use the @if statement inside @foreach

The foreach statement must be finished with @endforeach

Syntax:
```php
@foreach($arrayName as $varName)
  <div>@varName</div>
@endforeach
```

Example:

```php
$colors = ['blue','orange','green','red'];
$mount->mount('test',compact('colors'));
```

```php
<!-- test.mount -->
@foreach ($colors as $color)
    <div>@color</div>
@endforeach
```

## for cycles @for
You can use for statement with @for(condition) code.. @endfor The way of using @for is similar to way of using @foreach.

Here is the example:
```php
@for($i=0; $i<4; $i++)
    <div>This is @users[@i]['name'] and @i</div>
    @if($users[$i]['name'] == 'Alex')
        <div>Hello Alex @i welcome</div>
    @endif
@endfor
```

