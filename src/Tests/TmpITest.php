<?php

namespace Sim\Container\Tests;

class TmpITest
{
    protected $rnd_num;

    public function __construct(ITest $test_intetrface = null, float $random_number = 500)
    {
        $this->rnd_num = $random_number;
    }
}