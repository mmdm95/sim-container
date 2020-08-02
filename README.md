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

## Array accessing
You can use array accessing instead of method accessing:

- $container[$abstract] = $concrete instead of $container->set($abstract, $concrete).

- $concrete = $container[$abstract] instead of $concrete = $container->get($abstract).

- isset($container[$abstract]) instead of $container->has($abstract).

- unset($container[$abstract]) instead of $container->unset($abstract).

# License
Under MIT license.