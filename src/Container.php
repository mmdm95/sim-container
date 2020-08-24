<?php

namespace Sim\Container;

use ArrayAccess;
use Sim\Container\Traits\ContainerTrait;

class Container implements ContainerInterface, ArrayAccess
{
    use ContainerTrait;
}