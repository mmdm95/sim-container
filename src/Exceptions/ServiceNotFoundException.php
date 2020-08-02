<?php

namespace Sim\Container\Exceptions;


use Exception;
use Sim\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends Exception implements NotFoundExceptionInterface
{
    /**
     * ServiceNotFoundException constructor.
     * @param $service
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($service, $code = 0, Exception $previous = null)
    {
        $message = "Service {$service} is not found!";
        parent::__construct($message, $code, $previous);
    }
}