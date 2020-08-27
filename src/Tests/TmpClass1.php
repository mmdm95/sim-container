<?php

namespace Sim\Container\Tests;

class TmpClass1
{
    protected $cls;

    protected $rnd_num;

    public function __construct(TmpClass2 $cls, float $random_number = 500)
    {
        $this->cls = $cls;
        $this->rnd_num = $random_number;
    }

    public function showName(?string $name = null)
    {
        if(!is_null($name)) {
            echo $name;
        }
        echo $this->cls->getFullName() . ' (' . $this->rnd_num . ')' . PHP_EOL;
    }
}