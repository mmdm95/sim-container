<?php

namespace Sim\Container;

use ArrayAccess;
use Sim\Container\Interfaces\ContainerInterface;
use Sim\Container\Traits\ContainerTrait;

class Container implements ContainerInterface, ArrayAccess
{
    use ContainerTrait;

    /**
     * @var Container|null $instance
     */
    protected static $instance = null;

    /**
     * @return Container
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Container();
        }
        return self::$instance;
    }
}