<?php

namespace Sim\Container\Tests;

class TmpITest2 implements ITest2
{
    protected $name = 'Ted';

    public function __construct()
    {
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}