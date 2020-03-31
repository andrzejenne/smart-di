<?php

namespace BigBIT\SmartDI\Interfaces;


/**
 * Interface DependencyResolverInterface
 * @package BigBIT\SmartDI\Interfaces
 */
interface DependencyResolverInterface
{
    /**
     * @param string $id
     * @param SmartContainerInterface $container
     * @return mixed
     */
    public function getDependenciesFor(string $id, SmartContainerInterface $container);
}
