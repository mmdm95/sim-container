<?php

namespace Sim\Container\Tests;

class TmpClass3
{
    protected $name;

    public function __construct(string $name = 'Cooper')
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}