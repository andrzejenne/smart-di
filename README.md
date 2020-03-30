# smart-di
SmartContainer evolved from ODDIN example
Dependencies for php7.4

# Adding primitive dependencies
examples
```php
use BigBIT\SmartDI\SmartContainer;
use Psr\Container\ContainerInterface;

/** @var SmartContainer $container */
$container->setPrimitive(SomeClass::class, 'constructorPropertyName', 'someValue');
$container->setPrimitive(SomeClass::class, 'constructorPropertyName', 
    function(ContainerInterface $container) { 
        return 'someValue'; 
    }
);
``` 
