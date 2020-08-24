<?php

namespace Sim\Container;

use ArrayAccess;
use Sim\Container\Abstracts\AbstractSingleton;
use Sim\Container\Traits\ContainerTrait;

class ContainerSingleton extends AbstractSingleton implements ContainerInterface, ArrayAccess
{
    use ContainerTrait;
}