<?php

namespace Sim\Container;


use ArrayAccess;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Sim\Container\Exceptions\ParameterHasNoDefaultValueException;
use Sim\Container\Exceptions\ServiceNotFoundException;
use Sim\Container\Exceptions\ServiceNotInstantiableException;

class Container implements ContainerInterface, ArrayAccess
{
    /**
     * @var array $instances
     */
    private $instances = [];

    /**
     * @var array $resolvedServices
     */
    private $resolvedServices = [];

    /**
     * Set a service
     *
     * @param $abstract
     * @param null $concrete
     * @return Container
     */
    public function set($abstract, $concrete = null): Container
    {
        if (isset($this->resolvedServices[$abstract])) {
            unset($this->resolvedServices[$abstract]);
        }
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        $this->instances[$abstract] = $concrete;
        return $this;
    }

    /**
     * Get a service
     * Note: if service not registered, it'll be register then return
     *
     * @param $abstract
     * @return mixed|object|null
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotInstantiableException
     */
    public function get($abstract)
    {
        if (isset($this->resolvedServices[$abstract])) {
            return $this->resolvedServices[$abstract];
        }

        if (!isset($this->instances[$abstract])) {
            $this->set($abstract);
        }

        $entry = $this->instances[$abstract];
        if ($entry instanceof Closure) {
            $this->resolvedServices[$abstract] = $entry($this);
            return $this->resolvedServices[$abstract];
        }

        $this->resolvedServices[$abstract] = $this->resolve($entry);
        return $this->resolvedServices[$abstract];
    }

    /**
     * Get new instance each time
     *
     * @param $abstract
     * @return mixed|object
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotInstantiableException
     */
    public function make($abstract)
    {
        if (isset($this->instances[$abstract])) {
            $entry = $this->instances[$abstract];
            if ($entry instanceof Closure) {
                return $entry($this);
            }
        }

        return $this->resolve($abstract);
    }

    /**
     * Check if a service exists
     *
     * @param $abstract
     * @return bool
     */
    public function has($abstract): bool
    {
        if (isset($this->resolvedServices[$abstract])) {
            return true;
        }
        return isset($this->instances[$abstract]);
    }

    /**
     * Unset a registered service
     *
     * @param $abstract
     * @return Container
     */
    public function unset($abstract): Container
    {
        unset($this->resolvedServices[$abstract]);
        unset($this->instances[$abstract]);
        return $this;
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->set($value);
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }

    /**
     * Main resolve method to create & resolve dependencies recursively
     *
     * @param string $entry
     * @return object
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotInstantiableException
     */
    protected function resolve(string $entry)
    {
        $reflector = $this->getReflector($entry);
        $constructor = null;
        $resolved = [];
        $parameters = [];
        if ($reflector->isInstantiable()) {
            $constructor = $reflector->getConstructor();
            if (!is_null($constructor)) {
                $parameters = $constructor->getParameters();
            }
        } else {
            throw new ServiceNotInstantiableException($entry);
        }
        if (is_null($constructor) || empty($parameters)) {
            return $reflector->newInstance(); // return new instance from class
        }

        foreach ($parameters as $parameter) {
            $resolved[] = $this->resolveDependency($parameter);
        }
        return $reflector->newInstanceArgs($resolved); // return new instance with dependencies resolved
    }

    /**
     * Resolve dependency for a specific parameter
     *
     * @param ReflectionParameter $parameter
     * @return mixed|object
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     */
    protected function resolveDependency(ReflectionParameter $parameter)
    {
        if (!is_null($parameter->getClass())) { // The parameter is a class
            $typeName = $parameter->getType()->getName();
            if (!$this->isUserDefined($parameter)) { // The parameter is not user defined
                $this->set($typeName); // Register it
            }
            return $this->get($typeName); // Instantiate it
        } else { // The parameter is a built-in primitive type
            if ($parameter->isDefaultValueAvailable()) { // Check if default value for a parameter is available
                return $parameter->getDefaultValue(); // Get default value of parameter
            } else {
                throw new ParameterHasNoDefaultValueException($parameter->name);
            }
        }
    }

    /**
     * Get a reflection class for a specific entry/concrete
     *
     * @param $entry
     * @return ReflectionClass
     * @throws ServiceNotFoundException
     * @throws ServiceNotInstantiableException
     */
    protected function getReflector($entry)
    {
        try {
            $reflector = new ReflectionClass($entry);
            if (!$reflector->isInstantiable()) { // Check if class is instantiable
                throw new ServiceNotInstantiableException($entry);
            }
            return $reflector; // Return class reflector
        } catch (ReflectionException $ex) {
            throw new ServiceNotFoundException($entry);
        }
    }

    /**
     * Check if the parameter is defined by user or not
     *
     * @param ReflectionParameter $parameter
     * @return bool
     */
    protected function isUserDefined(ReflectionParameter $parameter)
    {
        if ($parameter->getType()->isBuiltin()) {
            return false;
        }
        $class = $parameter->getClass();
        $isUserDefined = !$class->isInternal();
        return $isUserDefined;
    }
}