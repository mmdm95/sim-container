<?php

use Sim\Container\Container;
use Sim\Container\ContainerSingleton;
use Sim\Container\Tests\TmpClass1;
use Sim\Container\Tests\TmpClass2;
use Sim\Container\Tests\TmpClass3;

include_once '../../vendor/autoload.php';

// Normal instantiating
$container = new Container();

// Singleton in normal instance
//$container = Container::getInstance();

// define instances
/**
 * Dependency injection will be automatically inject other classes to TmpClass1,
 * even when you didn't add it to the container!
 * @var TmpClass1 $tmpClass1
 */
$tmpClass1 = $container->get(TmpClass1::class);

// use functions of TmpClass1
$tmpClass1->showName(); // expected value is "Sheldon Cooper"

// this time store TmpClass1 with another parameters
$container->set(TmpClass2::class, function () {
    return new TmpClass2(new TmpClass3('John'), 'Doe');
});

// this is equivalent of set method in container
$container[TmpClass1::class] = function(Container $c) {
    return new TmpClass1($c->get(TmpClass2::class), random_int(1, 10000));
};

/**
 * @var TmpClass1 $tmpClass1
 */
$tmpClass1 = $container->get(TmpClass1::class);

echo PHP_EOL . 'This is TmpClass1 result of showName() method:' . PHP_EOL;

//use function of TmpClass1
$tmpClass1->showName(); // expected value is "John Doe"

/**
 * Get it one more time
 * @var TmpClass1 $tmpClass1
 */
$tmpClass1 = $container->get(TmpClass1::class);

echo PHP_EOL . 'This is TmpClass1 result of showName() method but it is previous resolved by container:' . PHP_EOL;

//use function of TmpClass1
$tmpClass1->showName(); // still expected value is "John Doe"

/**
 * Get it from factory
 * @var TmpClass1 $tmpClass1
 */
$tmpClass1 = $container->make(TmpClass1::class);

echo PHP_EOL . 'This is TmpClass1 result of showName() method but it is s new instance not resolved one:' . PHP_EOL;

//use function of TmpClass1
$tmpClass1->showName(); // still expected value is "John Doe" but it is another instance!

/**
 * Get it from factory another time
 * @var TmpClass1 $tmpClass1
 */
$tmpClass1 = $container->make(TmpClass1::class);

echo PHP_EOL . 'This is TmpClass1 result of showName() method but it is s new instance not resolved one again:' . PHP_EOL;

//use function of TmpClass1
$tmpClass1->showName(); // still expected value is "John Doe" but it is another instance!

/**
 * Get it normally one more time
 * @var TmpClass1 $tmpClass1
 */
$tmpClass1 = $container->get(TmpClass1::class);

echo PHP_EOL . 'This is TmpClass1 result of showName() method but it is previous resolved by container again:' . PHP_EOL;

//use function of TmpClass1
$tmpClass1->showName(); // still expected value is "John Doe" but look at the number!