<?php

namespace Sim\Container\Tests;

class TmpITest implements ITest
{
    protected $name;

    protected $rnd_num;

    public function __construct(ITest $test_interface = null, float $random_number = 500)
    {
        $this->rnd_num = $random_number;
    }

    public function setName(ITest2 $test_interface, ?string $name = 'Alexa')
    {
        if(!empty($name)) {
            $test_interface->setName($name);
        }
        echo $test_interface->getName();
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}