<?php

namespace Sim\Container\Tests;

class TmpClass2
{
    protected $cls;

    protected $family;

    public function __construct(TmpClass3 $cls, string $family = 'Sheldon')
    {
        $this->cls = $cls;
        $this->family = $family;
    }

    public function getFullName()
    {
        return $this->cls->getName() . ' ' . $this->family;
    }
}