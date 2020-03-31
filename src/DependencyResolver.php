<?php


namespace BigBIT\SmartDI;

use BigBIT\DIMeta\DIMetaResolver;
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
    /** @var DIMetaResolver */
    private DIMetaResolver $metaResolver;

    public function __construct(DIMetaResolver $metaResolver)
    {
        $this->metaResolver = $metaResolver;
    }

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
        $meta = $this->metaResolver->getClassMeta($id);

        $dependencies = [];

        foreach ($meta as $arg) {

            $dependency = null;

            if ($arg->isBuiltin) {
                $dependency = $container->getPrimitive($id, $arg->name);

                if ($arg->type) {
                    $dependencyType = gettype($dependency);
                    if ($dependencyType !== $arg->type) {
                        throw new DependencyException("Invalid dependency type `$dependencyType` for `$id` in `{$arg->name}`. It should be `{$arg->type}`.");
                    }
                }
            } else {
                if ($container->has($arg->type)) {
                    $dependency = $container->get($arg->type);
                }
            }

            if ($dependency === null && !$arg->allowsNull) {
                throw new DefinitionNotFoundException($id, $arg->name);
            }

            $dependencies[] = $dependency;
        }

        return $dependencies;
    }
}
