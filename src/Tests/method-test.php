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

//$name = $container->get(TmpClass1::class, 'showName', [
//    'name' => 'William ',
//]);

// Array structured
$info = [
    'abstract' => TmpClass1::class,
    'method' => [
        'name' => 'showName',
        'parameters' => [
            'name' => 'William '
        ],
    ],
];

// Object structured
//$info = new \stdClass();
//$info->abstract = TmpClass1::class;
//$info->method = [
//    'name' => 'showName',
//    'parameters' => [
//        'name' => 'William '
//    ],
//];

//$container[$info];
$container[json_encode($info)];

$container->set('a_simple_test', function () {
    return new \Sim\Container\Tests\TmpITest2();
});

$container->set(\Sim\Container\Tests\TmpITest::class, null, 'setName', [
    'test_interface' => 'a_simple_test',
//    'name' => '',
//    1 => 'mmdm',
]);
$name = $container->get(\Sim\Container\Tests\TmpITest::class, 'setName');
