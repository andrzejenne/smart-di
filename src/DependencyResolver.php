<?php


namespace BigBIT\SmartDI;


use BigBIT\SmartDI\Exceptions\DefinitionNotFoundException;
use BigBIT\SmartDI\Exceptions\DependencyException;
use BigBIT\SmartDI\Interfaces\DependencyResolverInterface;
use BigBIT\SmartDI\Interfaces\SmartContainerInterface;

/**
 * Class DependencyResolver
 * @package BigBIT\SmartDI
 */
class DependencyResolver implements DependencyResolverInterface
{
    /**
     * @param string $id
     * @param SmartContainerInterface $container
     * @return array
     * @throws DefinitionNotFoundException
     * @throws DependencyException
     * @throws \ReflectionException
     * @todo make di-chache reflection dependencies cache
     */
    public function getDependenciesFor(string $id, SmartContainerInterface $container) {
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
                    $dependency = $container->getPrimitive($id, $argName);

                    $dependencyType = gettype($dependency);
                    if ($dependencyType !== $typeName) {
                        throw new DependencyException("Invalid dependency type `$dependencyType` for `$id` in `$argName`. It should be `$typeName`.");
                    }
                } else {
                    if ($container->has($typeName)) {
                        $dependency = $container->get($typeName);
                    }
                }
            } else {
                $dependency = $container->getPrimitive($id, $argName);
            }

            if ($dependency === null && !$type->allowsNull()) {
                throw new DefinitionNotFoundException($id, $argName);
            }

            $dependencies[] = $dependency;
        }

        return $dependencies;
    }
}
