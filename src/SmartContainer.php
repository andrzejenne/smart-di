<?php

namespace BigBIT\SmartDI;

use BigBIT\DIMeta\DIMetaResolver;
use BigBIT\SmartDI\Exceptions\CannotRedefineException;
use BigBIT\SmartDI\Exceptions\CannotResolveException;
use BigBIT\SmartDI\Exceptions\ClassNotFoundException;
use BigBIT\SmartDI\Exceptions\DefinitionNotFoundException;
use BigBIT\SmartDI\Exceptions\DependencyException;
use BigBIT\SmartDI\Interfaces\DependencyResolverInterface;
use BigBIT\SmartDI\Interfaces\SmartContainerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class SmartContainer
 * @package BigBIT\SmartDI\Examples
 */
class SmartContainer implements SmartContainerInterface, \ArrayAccess
{
    /**
     * @param DependencyResolverInterface $dependencyResolver
     * @return SmartContainerInterface
     */
    public static function create(DependencyResolverInterface $dependencyResolver) {
        return new static($dependencyResolver);
    }

    /**
     * @param CacheInterface $cache
     * @return SmartContainerInterface
     */
    public static function createDefault(CacheInterface $cache)
    {
        return static::create(
            new DependencyResolver(
                new DIMetaResolver($cache)
            )
        );
    }

    /** @var array */
    private array $instances = [];

    /** @var array */
    private array $definitions = [];

    /** @var array */
    private array $primitives = [];

    /** @var DependencyResolverInterface|null */
    private ?DependencyResolverInterface $dependencyResolver;

    /**
     * SmartContainer constructor.
     * @param DependencyResolverInterface $dependencyResolver
     */
    public function __construct(DependencyResolverInterface $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }


    /**
     * @param string $id
     * @return mixed
     * @throws CannotResolveException
     * @throws ClassNotFoundException
     * @throws DefinitionNotFoundException
     * @throws CannotRedefineException
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
     * @throws CannotRedefineException
     * @throws ClassNotFoundException
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
     * @return SmartContainerInterface
     */
    public function bind(string $id, $instance)
    {
        $this->instances[$id] = $instance;

        return $this;
    }

    /**
     * @param string $id
     * @param callable $as
     * @return SmartContainerInterface
     * @throws CannotRedefineException
     */
    public function define(string $id, callable $as)
    {
        if(isset($this->definitions[$id])) {
            throw new CannotRedefineException($id);
        }

        $this->definitions[$id] = $as;

        return $this;
    }

    /**
     * @param string $id
     * @param string $name
     * @param mixed $value
     * @return SmartContainerInterface
     * @throws CannotRedefineException
     */
    public function definePrimitive(string $id, string $name, $value)
    {
        if (!isset($this->primitives[$id])) {
            $this->primitives[$id] = [];
        }

        if (isset($this->primitives[$id][$name])) {
            throw new CannotRedefineException($id);
        }

        $this->primitives[$id][$name] = $value;

        return $this;
    }

    /**
     * @param string $id
     * @param string $name
     * @return mixed|null
     */
    public function getPrimitive(string $id, string $name)
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
     * @throws ClassNotFoundException
     * @throws DefinitionNotFoundException
     * @throws CannotRedefineException
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
        if (is_callable($value)) {
            $this->define($offset, $value);
        } else {
            $this->bind($offset, $value);
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
     * @throws ClassNotFoundException
     * @throws CannotRedefineException
     */
    private function tryAutoWire(string $id)
    {
        if (class_exists($id)) {
            $this->define($id, function () use ($id) {
                try {
                    $dependencies = $this->dependencyResolver->getDependenciesFor($id, $this);

                    return new $id(...$dependencies);
                } catch (DependencyException $dependencyException) {
                    throw new CannotResolveException($id, 0, $dependencyException);
                }
            });
        } else {
            throw new ClassNotFoundException($id, "cannot auto wire");
        }
    }
}
