<?php

namespace Sim\Container\Traits;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Sim\Container\Exceptions\MethodNotFoundException;
use Sim\Container\Exceptions\ParameterHasNoDefaultValueException;
use Sim\Container\Exceptions\ServiceNotFoundException;
use Sim\Container\Exceptions\ServiceNotInstantiableException;

trait ContainerTrait
{
    /**
     * @var string $version
     */
    protected $version = '1.2.0';

    /**
     * @var array $instances
     */
    private $instances = [];

    /**
     * @var array $resolved_services
     */
    private $resolved_services = [];

    /**
     * @var array $method_instances
     */
    private $method_instances = [];

    /**
     * @var array $method_resolved_services
     */
    private $method_resolved_services = [];

    /**
     * @var array $method_parameters
     */
    private $method_parameters = [];

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set a service
     *
     * @param $abstract
     * @param null $concrete
     * @param string|null $method_name
     * @param array $method_parameters
     *
     * Specify kind of parameters it should have
     *
     * Exp1.
     *   [
     *     0 => CustomClass::class  or  Specified name in container,
     *     1 => AnotherCustomClass::class  or  Specified name in container,
     *   ]
     *
     * Exp2.
     * Also can specify parameter by set name of parameter to something you want
     *   [
     *     'parameter_name' => CustomClass::class  or  Specified name in container,
     *     'another_parameter_name' => AnotherCustomClass::class  or  Specified name in container,
     *   ]
     *
     * @return static
     */
    public function set($abstract, $concrete = null, ?string $method_name = null, array $method_parameters = [])
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!is_null($method_name)) {
            if (isset($this->method_resolved_services[$abstract][$method_name])) {
                unset($this->method_resolved_services[$abstract][$method_name]);
            }
            $this->method_parameters[$abstract][$method_name] = $method_parameters;
            $this->method_instances[$abstract][$method_name] = $concrete;
        } else {
            if (isset($this->resolved_services[$abstract])) {
                unset($this->resolved_services[$abstract]);
            }
            $this->instances[$abstract] = $concrete;
        }

        return $this;
    }

    /**
     * Get a service
     * Note: if service not registered, it'll be register then return
     *
     * @param $abstract
     * @param string|null $method_name
     * @param array $method_parameters
     * @return mixed|object|null
     * @throws MethodNotFoundException
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotInstantiableException
     */
    public function get($abstract, ?string $method_name = null, array $method_parameters = [])
    {
        if (!is_null($method_name)) {
            if (isset($this->method_resolved_services[$abstract][$method_name])) {
                return $this->method_resolved_services[$abstract][$method_name];
            }

            if (!isset($this->instances[$abstract][$method_name])) {
                $this->set($abstract, null, $method_name, $this->method_parameters[$abstract][$method_name] ?? []);
            }

            $this->method_resolved_services[$abstract][$method_name] = $this->make($abstract, $method_name, $method_parameters);
            $resolved = $this->method_resolved_services[$abstract][$method_name];
        } else {
            if (isset($this->resolved_services[$abstract])) {
                return $this->resolved_services[$abstract];
            }

            if (!isset($this->instances[$abstract])) {
                $this->set($abstract);
            }

            $this->resolved_services[$abstract] = $this->make($abstract);
            $resolved = $this->resolved_services[$abstract];
        }

        return $resolved;
    }

    /**
     * Get new instance each time
     *
     * @param $abstract
     * @param string|null $method_name
     * @param array $method_parameters
     * @return mixed|object
     * @throws MethodNotFoundException
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotInstantiableException
     */
    public function make($abstract, ?string $method_name = null, array $method_parameters = [])
    {
        $parameters = [];
        if (!is_null($method_name)) {
            if (!isset($this->method_instances[$abstract][$method_name])) {
                $this->method_instances[$abstract][$method_name] = $abstract;
            }

            $parameters = !empty($method_parameters)
                ? $method_parameters
                : ($this->method_parameters[$abstract][$method_name] ?? []);

            $entry = $this->method_instances[$abstract][$method_name];
        } else {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $abstract;
            }

            $entry = $this->instances[$abstract];
        }

        if ($entry instanceof Closure) {
            return $entry($this);
        }

        return $this->resolve($entry, $method_name, $parameters);
    }

    /**
     * Check if a service exists
     *
     * @param $abstract
     * @param string|null $method_name
     * @return bool
     */
    public function has($abstract, string $method_name = null): bool
    {
        if (!is_null($method_name)) {
            if (isset($this->method_resolved_services[$abstract][$method_name])) {
                return true;
            }
            return isset($this->method_instances[$abstract][$method_name]);
        } else {
            if (isset($this->resolved_services[$abstract])) {
                return true;
            }
            return isset($this->instances[$abstract]);
        }
    }

    /**
     * Unset a registered service
     *
     * @param $abstract
     * @param string|null $method_name
     * @return static
     */
    public function unset($abstract, string $method_name = null)
    {
        if (!is_null($method_name)) {
            unset($this->method_resolved_services[$abstract][$method_name]);
            unset($this->method_instances[$abstract][$method_name]);
        } else {
            unset($this->resolved_services[$abstract]);
            unset($this->instances[$abstract]);
        }

        return $this;
    }

    /**
     * Whether a offset exists
     *
     * Accepts $offset as below:
     *   [
     *     'abstract' => $abstract,
     *     'method' => [
     *       'name' => method's name,
     *     ],
     *   ]
     *
     * OR:
     *   object(stdClass) {
     *     'abstract' => $abstract,
     *     'method' => [
     *       'name' => method's name,
     *     ],
     *   }
     *
     * OR:
     *   An encoded json that has above structure
     *
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
        $actualOffset = $offset;

        $methodName = null;
        $abstract = $offset;

        // if json passed
        if (is_string($offset)) {
            $str = json_decode($offset, true);
            if (!is_null($str)) {
                $offset = $str;
            }
        }

        if (is_array($offset)) {
            if(isset($offset['abstract'])) {
                $abstract = $offset['abstract'];
                if(isset($offset['method']['name'])) {
                    $methodName = $offset['method']['name'];
                }
            } else {
                $abstract = $actualOffset;
            }
        } elseif (is_object($offset) && isset($offset->abstract) && isset($offset->method['name'])) {
            if(isset($offset->abstract)) {
                $abstract = $offset->abstract;
                if(isset($offset->method['name'])) {
                    $methodName = $offset->method['name'];
                }
            } else {
                $abstract = $actualOffset;
            }
        }

        return $this->has($abstract, $methodName);
    }

    /**
     * Offset to retrieve
     *
     * Accepts $offset as below:
     *   [
     *     'abstract' => $abstract,
     *     'method' => [
     *       'name' => method's name,
     *       'parameters' => [
     *         method_parameter1 => CustomClass::class  or  Specified name in container,
     *         method_parameter2 => AnotherCustomClass::class  or  Specified name in container
     *       ].
     *     ],
     *   ]
     *
     * OR:
     *   object(stdClass) {
     *     'abstract' => $abstract,
     *     'method' => [
     *       'name' => method's name,
     *       'parameters' => [
     *         method_parameter1 => CustomClass::class  or  Specified name in container,
     *         method_parameter2 => AnotherCustomClass::class  or  Specified name in container
     *       ].
     *     ],
     *   }
     *
     * OR:
     *   An encoded json that has above structure
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @throws MethodNotFoundException
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotInstantiableException
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        $actualOffset = $offset;

        $methodName = null;
        $methodParameters = [];
        $abstract = $offset;

        // if json passed
        if (is_string($offset)) {
            $str = json_decode($offset, true);
            if (!is_null($str)) {
                $offset = $str;
            }
        }

        if (is_array($offset)) {
            if (isset($offset['abstract'])) {
                $abstract = $offset['abstract'];

                if (isset($offset['method']['name'])) {
                    $methodName = $offset['method']['name'];

                    if (isset($offset['method']['parameters']) && is_array($offset['method']['parameters'])) {
                        $methodParameters = $offset['method']['parameters'];
                    }
                }
            } else {
                $abstract = $actualOffset;
            }
        } elseif (is_object($offset)) {
            if (isset($offset->abstract)) {
                $abstract = $offset->abstract;

                if (isset($offset->method['name'])) {
                    $methodName = $offset->method['name'];

                    if (isset($offset->method['parameters']) && is_array($offset->method['parameters'])) {
                        $methodParameters = $offset->method['parameters'];
                    }
                }
            } else {
                $abstract = $actualOffset;
            }
        }

        return $this->get($abstract, $methodName, $methodParameters);
    }

    /**
     * Offset to set
     *
     * Accepts $offset as below:
     *   [
     *     'concrete' => $concrete,
     *     'method' => [
     *       'name' => method's name,
     *       'parameters' => [
     *         method_parameter1 => CustomClass::class  or  Specified name in container,
     *         method_parameter2 => AnotherCustomClass::class  or  Specified name in container
     *       ].
     *     ],
     *   ]
     *
     * OR:
     *   object(stdClass) {
     *     'concrete' => $concrete,
     *     'method' => [
     *       'name' => method's name,
     *       'parameters' => [
     *         method_parameter1 => CustomClass::class  or  Specified name in container,
     *         method_parameter2 => AnotherCustomClass::class  or  Specified name in container
     *       ].
     *     ],
     *   }
     *
     * OR:
     *   An encoded json that has above structure
     *
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
        $methodName = null;
        $methodParameters = [];

        if (is_null($offset)) {
            $actualOffset = $value;
            $abstract = $value;
            $concrete = null;
        } else {
            $actualOffset = $offset;
            $abstract = $offset;
            $concrete = $value;
        }

        // if json passed
        if (is_string($offset)) {
            $str = json_decode($offset, true);
            if (!is_null($str)) {
                $offset = $str;
            }
        }

        if (is_array($value)) {
            if (isset($value['concrete'])) {
                if (is_null($offset)) {
                    $abstract = $value['concrete'];
                } else {
                    $concrete = $value['concrete'];
                }

                if (isset($value['method']['name'])) {
                    $methodName = $value['method']['name'];

                    if (isset($value['method']['parameters']) && is_array($value['method']['parameters'])) {
                        $methodParameters = $value['method']['parameters'];
                    }
                }
            } else {
                $abstract = $actualOffset;
            }
        } elseif (is_object($offset)) {
            if (isset($value->concrete)) {
                if (is_null($offset)) {
                    $abstract = $value->concrete;
                } else {
                    $concrete = $value->concrete;
                }

                if (isset($value->method['name'])) {
                    $methodName = $value->method['name'];

                    if (isset($value->method['parameters']) && is_array($value->method['parameters'])) {
                        $methodParameters = $value->method['parameters'];
                    }
                }
            } else {
                $abstract = $actualOffset;
            }
        }

        $this->set($abstract, $concrete, $methodName, $methodParameters);
    }

    /**
     * Offset to unset
     *
     * Accepts $offset as below:
     *   [
     *     'abstract' => $abstract,
     *     'method' => [
     *       'name' => method's name,
     *     ],
     *   ]
     *
     * OR:
     *   object(stdClass) {
     *     'abstract' => $abstract,
     *     'method' => [
     *       'name' => method's name,
     *     ],
     *   }
     *
     * OR:
     *   An encoded json that has above structure
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $actualOffset = $offset;

        $methodName = null;
        $abstract = $offset;

        // if json passed
        if (is_string($offset)) {
            $str = json_decode($offset, true);
            if (!is_null($str)) {
                $offset = $str;
            }
        }

        if (is_array($offset)) {
            if(isset($offset['abstract'])) {
                $abstract = $offset['abstract'];
                if(isset($offset['method']['name'])) {
                    $methodName = $offset['method']['name'];
                }
            } else {
                $abstract = $actualOffset;
            }
        } elseif (is_object($offset) && isset($offset->abstract) && isset($offset->method['name'])) {
            if(isset($offset->abstract)) {
                $abstract = $offset->abstract;
                if(isset($offset->method['name'])) {
                    $methodName = $offset->method['name'];
                }
            } else {
                $abstract = $actualOffset;
            }
        }

        $this->unset($abstract, $methodName);
    }

    /**
     * Main resolve method to create & resolve dependencies recursively
     *
     * @param string $entry
     * @param string|null $method_name
     * @param array $method_parameters
     * @return object
     * @throws MethodNotFoundException
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotInstantiableException
     */
    protected function resolve(string $entry, ?string $method_name = null, array $method_parameters = [])
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
            $instance = $reflector->newInstance(); // return new instance from class
            return $this->resolveMethod($reflector, $instance, $method_name, $method_parameters);
        }

        foreach ($parameters as $parameter) {
            $resolved[] = $this->resolveDependency($parameter);
        }
        $instance = $reflector->newInstanceArgs($resolved); // return new instance with dependencies resolved
        return $this->resolveMethod($reflector, $instance, $method_name, $method_parameters);
    }

    /**
     * @param ReflectionClass $reflector
     * @param $instance
     * @param string|null $method_name
     * @param array $method_parameters
     * @return mixed
     * @throws MethodNotFoundException
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     */
    protected function resolveMethod(ReflectionClass $reflector, $instance, ?string $method_name = null, array $method_parameters = [])
    {
        // method injection if method specified
        if (!is_null($method_name)) {
            // check if method is exists in instance
            if (!$reflector->hasMethod($method_name)) {
                throw new MethodNotFoundException(['name' => $method_name, 'class' => $reflector->getName()]);
            }

            $methodReflection = $reflector->getMethod($method_name);

            $passingInstance = !$methodReflection->isStatic() ? $instance : null;

            if ($methodReflection->isPublic()) {
                $methodParametersResolved = [];
                $methodParameters = $methodReflection->getParameters();

                /**
                 * @var ReflectionParameter $parameter
                 */
                foreach ($methodParameters as $k => $parameter) {
                    $defOrClass = null;
                    if (isset($method_parameters[$parameter->getName()]) || isset($method_parameters[$k])) {
                        $defOrClass = $method_parameters[$parameter->getName()] ?? $method_parameters[$k];
                    }

                    $methodParametersResolved[] = $this->resolveDependency($parameter, $defOrClass);
                }
                return $methodReflection->invokeArgs($passingInstance, $methodParametersResolved);
            }
        }

        // otherwise just do constructor injection
        return $instance;
    }

    /**
     * Resolve dependency for a specific parameter
     *
     * @param ReflectionParameter $parameter
     * @param null $defOrClass
     * @return mixed|object
     * @throws ParameterHasNoDefaultValueException
     * @throws ReflectionException
     */
    protected function resolveDependency(ReflectionParameter $parameter, $defOrClass = null)
    {
        if (!is_null($parameter->getClass())) { // The parameter is a class
            $typeName = $parameter->getType()->getName();
            if (!$this->isUserDefined($parameter)) { // The parameter is not user defined
                $this->set($typeName); // Register it
            }

            if (!is_null($defOrClass) && $parameter->getClass()->isInterface()) {
                try {
                    $reflect = new ReflectionClass($defOrClass);
                    if (!$reflect->isUserDefined()) {
                        $this->set($defOrClass); // Register it
                    }

                    return $this->get($defOrClass); // Instantiate it
                } catch (\Exception $e) {
                    if (!is_null($defOrClass)) {
                        return $defOrClass;
                    } else {
                        if ($parameter->isDefaultValueAvailable()) { // Check if default value for a parameter is available
                            return $parameter->getDefaultValue(); // Get default value of parameter
                        } else {
                            throw new ParameterHasNoDefaultValueException($parameter->name);
                        }
                    }
                }
            } else {
                try {
                    return $this->get($typeName); // Instantiate it
                } catch (\Exception $e) {
                    if ($parameter->isDefaultValueAvailable()) { // Check if default value for a parameter is available
                        return $parameter->getDefaultValue(); // Get default value of parameter
                    } else {
                        throw new ParameterHasNoDefaultValueException($parameter->name);
                    }
                }
            }
        } else { // The parameter is a built-in primitive type
            if (!is_null($defOrClass)) {
                return $defOrClass;
            } else {
                if ($parameter->isDefaultValueAvailable()) { // Check if default value for a parameter is available
                    return $parameter->getDefaultValue(); // Get default value of parameter
                } else {
                    throw new ParameterHasNoDefaultValueException($parameter->name);
                }
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