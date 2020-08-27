<?php

namespace Sim\Container\Exceptions;

use Exception;
use Sim\Container\Interfaces\NotFoundExceptionInterface;

class MethodNotFoundException extends Exception implements NotFoundExceptionInterface
{
    /**
     * ServiceNotFoundException constructor.
     * @param $method
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($method, $code = 0, Exception $previous = null)
    {
        $message = "Method {$method['name']} is not found in {$method['class']}!";
        parent::__construct($message, $code, $previous);
    }
}