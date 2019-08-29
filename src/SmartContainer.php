<?php


namespace BigBIT\SmartDI;


use BigBIT\SmartDI\Exceptions\CannotResolveException;
use BigBIT\SmartDI\Exceptions\ClassNotFoundException;
use BigBIT\SmartDI\Exceptions\DefinitionNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * Class SmartContainer
 * @package BigBIT\SmartDI\Examples
 * @todo - create dedicated exceptions
 */
class SmartContainer implements ContainerInterface, \ArrayAccess
{

    /** @var array */
    private $definitions = [];

    /** @var array */
    private $instances = [];

    /**
     * @param string $id
     * @return mixed
     * @throws DefinitionNotFoundException
     * @throws CannotResolveException
     * @throws \Exception
     */
    public function get($id)
    {
        if (!isset($this->instances[$id])) {
            if (!isset($this->definitions[$id])) {
                $this->tryAutoWire($id);
            }

            if (isset($this->definitions[$id])) {
                try {
                    $this->instances[$id] = $this->definitions[$id]($this);
                } catch (\Throwable $throwable) {
                    throw new CannotResolveException($id, 0, $throwable);
                }
            } else {
                throw new DefinitionNotFoundException($id);
            }
        }

        return $this->instances[$id];
    }

    /**
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function has($id)
    {
        if (!isset($this->definitions[$id])) {
            $this->tryAutoWire($id);
        };

        return isset($this->definitions[$id]);
    }

    /**
     * @param string $id
     * @param mixed $instance
     */
    public function bind($id, $instance)
    {
        $this->instances[$id] = $instance;
        $this->definitions[$id] = true;
    }

    /**
     * @param mixed $offset
     * @return bool
     * @throws \Exception
     */
    public function offsetExists($offset)
    {
        return isset($this->definitions[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     * @throws CannotResolveException
     * @throws DefinitionNotFoundException
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === 'definitions' || $offset === 'instances') {
            throw new \Exception('Overwriting private properties is forbidden');
        }

        if (is_callable($value)) {
            $this->definitions[$offset] = $value;
        } else {
            $this->definitions[$offset] = true;
            $this->instances[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        throw new \Exception('No unset ;)');
    }

    /**
     * @param string $id
     * @throws \Exception
     */
    private function tryAutoWire(string $id)
    {
        if (class_exists($id)) {
            $this[$id] = function () use ($id) {
                $dependencies = $this->getDependenciesFor($id);

                return new $id(...$dependencies);
            };
        } else {
            throw new ClassNotFoundException($id, "cannot auto wire");
        }
    }

    /**
     * @param string $id
     * @return array
     * @throws CannotResolveException
     * @throws DefinitionNotFoundException
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function getDependenciesFor(string $id) {
        $reflection = new \ReflectionClass($id);

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return [];
        }

        $parameters = $constructor->getParameters();

        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            if (!$type) {
                throw new \Exception("Parameter " . $parameter->getName() . " has no type specified, cannot auto wire");
            } else {
                if ($type->allowsNull()) {
                    $dependencies[] = null;
                } else {
                    $typeName = $type->getName();

                    if ($typeName) {
                        if ($type->isBuiltin()) {
                            throw new \Exception("Cannot auto wire builtin type " . $type->getName() . " in $id");
                        } else {
                            if ($this->has($typeName)) {
                                $dependencies[] = $this->get($typeName);
                            } else {
                                throw new \Exception("Cannot auto wire unknown type $typeName for $id");
                            }
                        }
                    } else {
                        throw new \Exception("Cannot continue, no type name specified for $id");
                    }
                }
            }
        }

        return $dependencies;
    }
}
