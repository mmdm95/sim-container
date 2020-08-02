<?php

namespace Sim\Container\Exceptions;


use Exception;
use Sim\Container\ContainerExceptionInterface;

class ServiceNotInstantiableException extends Exception implements ContainerExceptionInterface
{
    /**
     * ServiceNotInstantiableException constructor.
     * @param $service
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($service, $code = 0, Exception $previous = null)
    {
        $message = "Service {$service} is not instantiable";
        parent::__construct($message, $code, $previous);
    }
}