<?php

namespace Sim\Container\Tests;

interface ITest
{
    public function setName(ITest2 $test_interface, string $name);

    public function getName();
}