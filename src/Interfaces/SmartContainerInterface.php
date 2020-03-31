<?php

namespace BigBIT\SmartDI\Interfaces;


use BigBIT\SmartDI\Exceptions\CannotRedefineException;
use Psr\Container\ContainerInterface;

/**
 * Interface DependencyResolverInterface
 * @package BigBIT\SmartDI\Interfaces
 */
interface SmartContainerInterface extends ContainerInterface
{
    /**
     * @param string $id
     * @param $value
     * @return SmartContainerInterface
     */
    public function bind(string $id, $value);

    /**
     * @param string $id
     * @param callable $as
     * @return SmartContainerInterface
     * @throws CannotRedefineException
     */
    public function define(string $id, callable $as);

    /**
     * @param string $id
     * @param string $name
     * @param $value
     * @return SmartContainerInterface
     * @throws CannotRedefineException
     */
    public function definePrimitive(string $id, string $name, $value);

    /**
     * @param string $id
     * @param string $name
     * @return mixed
     */
    public function getPrimitive(string $id, string $name);
}
