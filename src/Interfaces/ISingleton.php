<?php

namespace Sim\Container\Interfaces;

interface ISingleton {
    /**
     * @return mixed
     */
    public static function getInstance();
}
