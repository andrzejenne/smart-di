# smart-di

SmartContainer evolved from ODDIN example.

Dependencies for php7.4

## Installation

composer require bigbit/smart-di

## Usage

Creating instance using static createDefault factory method.
```php
use Symfony\Component\Cache\Simple\ArrayCache;

/** @var Psr\SimpleCache\CacheInterface */
$cache = new ArrayCache();

$container = SmartContainer::createDefault($cache);
```

## Overriding service definitions

By default, all definitions are discovered automatically.

To force a service factory callable into container, use define method.

```php
$container->define(SomeClass::class, function(ContainerInterface $container) {
    return new SomeClass(
        $container->get(SomeDependency::class)
    );
});
```

## Adding primitive dependencies
In some circumstances, service requires primitive value in constructor.

SomeClass service can look like this.
```php
class SomeClass {
    public function __construct(SomeService $service, string $primitive, $mixed) { }
}
```
Adding primitive by value or callable.
```php
use BigBIT\SmartDI\Interfaces\SmartContainerInterface;
use Psr\Container\ContainerInterface;

/** @var SmartContainerInterface $container */
$container->definePrimitive(SomeClass::class, 'primitive', 'someValue');
$container->definePrimitive(SomeClass::class, 'mixed', 
    function(ContainerInterface $container) { 
        return 'anotherValue';
    }
);
```
