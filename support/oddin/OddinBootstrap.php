<?php
namespace BigBIT\SmartDI\Support\oddin;

use BigBIT\DIBootstrap\Bootstrap;
use BigBIT\DIBootstrap\Exceptions\ClassNotFoundException;
use BigBIT\DIBootstrap\Exceptions\InvalidContainerImplementationException;
use BigBIT\DIBootstrap\Exceptions\PathNotFoundException;
use BigBIT\Oddin\Singletons\DIResolver;
use BigBIT\Oddin\Utils\CacheResolver;
use BigBIT\Oddin\Utils\ClassMapResolver;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Class OddinBootstrap
 * @package BigBIT\Oddin\Support\oddin
 */
class OddinBootstrap extends Bootstrap
{
    /**
     * @param array $bindings
     * @throws ClassNotFoundException
     * @throws InvalidContainerImplementationException
     * @throws PathNotFoundException
     */
    protected static function boot(array $bindings) {
        parent::boot($bindings);

        DIResolver::create(static::$container);
    }

    /**
     * @return array
     */
    protected static function getDefaultBindings() {
        return [
            CacheInterface::class => function () {
                return new Psr16Cache(new ArrayAdapter());
            },
            ClassMapResolver::class => function () {
                return new ClassMapResolver(static::getAutoloadPath());
            },
            CacheResolver::class => function (ContainerInterface $container) {
                return new CacheResolver(
                    $container->get(ClassMapResolver::class),
                    $container->get(CacheInterface::class)
                );
            },
        ];
    }
}
