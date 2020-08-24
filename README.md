# Simplicity Container
A library for dependency injection management.

## Features
- Auto wiring
- Singleton construction
- Factory construction
- Both method and array access

## Install
**composer**
```php 
composer require mmdm/sim-container
```

Or you can simply download zip file from github and extract it, 
then put file to your project library and use it like other libraries.

## How to use
```php
// to instance a container object
$container = new Container();
// here you can define your object and use them
```

## Available functions

#### Container

- set($abstract, $concrete = null): Container

This method store a $concrete with alias of $abstract 
like class name or any other name.

Note: You can pass $concrete as $abstract

```php
// store a class
$container->set('log_cls', Logger::class);
// or simply store with Logger::class string
// alias will be the string from Logger::class
$container->set(Logger::class);
```

- get($abstract)

This method return a $concrete that stored with alias of 
$abstract. If it was not exists, it will simply store it first 
and then resolve that.

Note: If a stored $concrete has been resolved, it will not 
resolve again, just return previous resolved $concrete

```php
// retrieve a class
$container->get('log_cls');
// or
$container->get(NotStoredClass::class);
```

- make($abstract)

This method return a $concrete that stored with alias of 
$abstract. If it was not exists, it will simply store it first 
and then resolve that.

```php
// retrieve new instance of a class
$container->make('log_cls');
// or
$container->make(NotStoredClass::class);
```

- has($abstract): bool

This method check if a specific alias $abstract is stored or not.

```php
// check existence of a $abstract
$container->has('log_cls');
// or
$container->has(NotStoredClass::class);
```

- unset($abstract): Container

This method remove a stored $abstract.

```php
// remove an $abstract
$container->unset('log_cls');
// or
$container->unset(NotStoredClass::class);
```

#### Singleton accessing

If you need to access container in singleton manner, use 
`getInstance` static method.

```php
$container = Container::getInstance();

// then use all methods
$container->get($abstract);

//...
```

#### Container Inheritance

If you want to inherit `Container` (to customize maybe), you 
can inherit like normal way but if you have multiple class to 
inherit, then use `ContainerTrait` inside your class and inherit 
other class as well.

Note: After a trait using, you should type hint `Container` 
to `YourOtherClass` in closures.

```php
class YourOtherClass extends AnotherClass {
    use ContainerTrait;
    
    // other codes
}

$instance = new YourOtherClass();

// the difference is in [YourOtherClass] instead of [Container]
$instance->set($abstract, function (YourOtherClass $c) {
    // some code
});
```

## Array accessing
You can use array accessing instead of method accessing:

- $container[$abstract] = $concrete instead of $container->set($abstract, $concrete).

- $concrete = $container[$abstract] instead of $concrete = $container->get($abstract).

- isset($container[$abstract]) instead of $container->has($abstract).

- unset($container[$abstract]) instead of $container->unset($abstract).

# License
Under MIT license.