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
    private array $definitions = [];

    /** @var array */
    private array $instances = [];

    /** @var array */
    private array $primitives = [];

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
    public function bind(string $id, $instance)
    {
        $this->instances[$id] = $instance;
        $this->definitions[$id] = true;
    }

    /**
     * @param string $cls
     * @param string $name
     * @param mixed $value
     * @return SmartContainer
     */
    public function setPrimitive(string $cls, string $name, $value)
    {
        if (!isset($this->primitives[$cls])) {
            $this->primitives[$cls] = [];
        }

        if (!isset($this->primitives[$cls][$name])) {
            $this->primitives[$cls][$name] = $value;
        }

        return $this;
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

            $dependency = null;

            $type = $parameter->getType();
            $argName = $parameter->getName();

            if ($type instanceof \ReflectionNamedType) {
                $typeName = $type->getName();
                if ($type->isBuiltin()) {
                    $dependency = $this->getPrimitiveDependency($id, $argName);

                    $dependencyType = gettype($dependency);
                    if ($dependencyType !== $typeName) {
                        throw new \Exception("Invalid dependency type `$dependencyType` for `$id` in `$argName`. It should be `$typeName`.");
                    }
                } else {
                    if ($this->has($typeName)) {
                        $dependency = $this->get($typeName);
                    }
                }
            } else {
                $dependency = $this->getPrimitiveDependency($id, $argName);
            }

            if ($dependency === null && !$type->allowsNull()) {
                throw new \Exception("Dependency `$argName` for `$id` not found.");
            }

            $dependencies[] = $dependency;
        }

        return $dependencies;
    }

    /**
     * @param string $id
     * @param string $name
     * @return mixed|null
     */
    private function getPrimitiveDependency(string $id, string $name)
    {
        $dependency = null;
        if (isset($this->primitives[$id][$name])) {
            $dependency = $this->primitives[$id][$name];
            if (is_callable($dependency)) {
                $dependency = $dependency($this);
            }
        }

        return $dependency;
    }
}
