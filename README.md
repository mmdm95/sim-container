# Simplicity Container
A library for dependency injection management.

## Features
- Auto wiring
- Singleton construction
- Factory construction
- Method injection
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

#### `set($abstract, $concrete = null, ?string $method_name = null, array $method_parameters = []): Container`

This method store a $concrete with alias of $abstract 
like class name or any other name.

`Version >= 1.2.0`:
Also you can pass the method to be inject as $method_name and 
parameters of the method as $method_parameters. Parameters can be 
the actual parameter or an abstract to get by container.

Note: You can pass $concrete as $abstract

Note: Parameters are a key value pair like below:
```
parameter_name => your assignment,
index_of_parameter like 0|1|2|... => your assignment,
```
```
// example
'class_type' => CustomClass::class,
1 => 'A parameter',
```

----------

```php
// store a class
$container->set('log_cls', Logger::class);
// or simply store with Logger::class string
// alias will be the string from Logger::class
$container->set(Logger::class);

// method injection
$container->set('log_cls', Logger::class, 'info');

// or with parameters
$container->set('log_cls', Logger::class, 'info', [
    'handler' => Handler::class,
    1 => '{level} - {other_parameter} - {message}',
]);
```

#### `get($abstract, ?string $method_name = null, array $method_parameters = [])`

This method return a $concrete that stored with alias of 
$abstract. If it was not exists, it will simply store it first 
and then resolve that.

`Version >= 1.2.0`:
Also you can pass the method to be inject as $method_name and 
parameters of the method as $method_parameters. Parameters can be 
the actual parameter or an abstract to get by container.

Note: If a stored $concrete has been resolved, it will not 
resolve again, just return previous resolved $concrete.

Note: Parameters are a key value pair like below:
```
parameter_name => your assignment,
index_of_parameter like 0|1|2|... => your assignment,
```
```
// example
'class_type' => CustomClass::class,
1 => 'A parameter',
```

----------

```php
// retrieve a class
$container->get('log_cls');
// or
$container->get(NotStoredClass::class);
```

#### `make($abstract, ?string $method_name = null, array $method_parameters = [])`

This method return a $concrete that stored with alias of 
$abstract. If it was not exists, it will simply store it first 
and then resolve that.

Parameters are same as `get` method

```php
// retrieve new instance of a class
$container->make('log_cls');
// or
$container->make(NotStoredClass::class);
```

#### `has($abstract, string $method_name = null): bool`

This method check if a specific alias $abstract is stored or not. Also 
you can pass the `$method_name` to check if specific `$abstract`'s 
method is registered.

```php
// check existence of a $abstract
$container->has('log_cls');
// or
$container->has(NotStoredClass::class);
```

#### `unset($abstract, string $method_name = null): Container`

This method remove a stored $abstract. Also you can 
pass the `$method_name` to remove specific `$abstract`'s 
method.

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

- $container[$abstract] = $concrete instead of 
$container->set($abstract, $concrete, ?string $method_name = null, array $method_parameters = []).

Accepts $offset as below:
  [
    'concrete' => $concrete,
    'method' => [
      'name' => method's name,
      'parameters' => [
        method_parameter1 => CustomClass::class  or  Specified name in container,
        method_parameter2 => AnotherCustomClass::class  or  Specified name in container
      ].
    ],
  ]

OR

  object(stdClass) {
    'concrete' => $concrete,
    'method' => [
      'name' => method's name,
      'parameters' => [
        method_parameter1 => CustomClass::class  or  Specified name in container,
        method_parameter2 => AnotherCustomClass::class  or  Specified name in container
      ].
    ],
  }

OR

  An encoded json that has above structure

OR

  An optional $abstract variable and $concrete/$abstract value

----------

- $concrete = $container[$abstract] instead of 
$concrete = $container->get($abstract, ?string $method_name = null, array $method_parameters = []).

Accepts $offset as below:
  [
    'abstract' => $abstract,
    'method' => [
      'name' => method's name,
      'parameters' => [
        method_parameter1 => CustomClass::class  or  Specified name in container,
        method_parameter2 => AnotherCustomClass::class  or  Specified name in container
      ].
    ],
  ]
  
OR

  object(stdClass) {
    'abstract' => $abstract,
    'method' => [
      'name' => method's name,
      'parameters' => [
        method_parameter1 => CustomClass::class  or  Specified name in container,
        method_parameter2 => AnotherCustomClass::class  or  Specified name in container
      ].
    ],
  }
  
OR

  An encoded json that has above structure

OR

  An $abstract variable
  
----------

- isset($container[$abstract]) instead of 
$container->has($abstract, string $method_name = null).

Accepts $offset as below:
  [
    'abstract' => $abstract,
    'method' => [
      'name' => method's name,
    ],
  ]
  
OR

  object(stdClass) {
    'abstract' => $abstract,
    'method' => [
      'name' => method's name,
    ],
  }
  
OR

  An encoded json that has above structure
  
OR

  An $abstract variable
  
----------

- unset($container[$abstract]) instead of 
$container->unset($abstract, string $method_name = null).

Accepts $offset as below:
  [
    'abstract' => $abstract,
    'method' => [
      'name' => method's name,
    ],
  ]
  
OR

  object(stdClass) {
    'abstract' => $abstract,
    'method' => [
      'name' => method's name,
    ],
  }
  
OR

  An encoded json that has above structure
  
OR

  An $abstract variable

# License
Under MIT license.