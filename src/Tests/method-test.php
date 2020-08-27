<?php

use Sim\Container\Container;
use Sim\Container\Tests\TmpClass1;

include_once '../../vendor/autoload.php';

// Normal instantiating
$container = new Container();

/**
 * Dependency injection will be automatically inject other classes to TmpClass1,
 * even when you didn't add it to the container!
 * @var TmpClass1 $tmpClass1
 */
//$tmpClass1 = $container->get(TmpClass1::class);

// use functions of TmpClass1
//$tmpClass1->showName(); // expected value is "Sheldon Cooper"

$name = $container->get(TmpClass1::class, 'showName', [
    'name' => 'William ',
]);

$container->set(\Sim\Container\Tests\TmpITest::class, null, 'setName', [
    'test_interface' => \Sim\Container\Tests\TmpITest2::class,
    'name' => 'mmdm',
]);
$name = $container->get(\Sim\Container\Tests\TmpITest::class, 'setName');
