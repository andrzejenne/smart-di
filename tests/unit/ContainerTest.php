<?php

use BigBIT\example\ExtClass;
use BigBIT\SmartDI\SmartContainer;
use Symfony\Component\Cache\Simple\ArrayCache;

/**
 * Class ExampleClass
 */
class ExampleClass {

}

/**
 * Class AutowireClass
 */
class AutowireClass {

    public ExampleClass $example;

    public function __construct(ExampleClass $example)
    {
        $this->example = $example;
    }
}

/**
 * Class PrimitiveClass
 */
class PrimitiveClass {

    public string $typedPrimitive;

    /** @var mixed */
    public $mixedPrimitive;

    public function __construct(string $typedPrimitive, $mixedPrimitive)
    {
        $this->typedPrimitive = $typedPrimitive;
        $this->mixedPrimitive = $mixedPrimitive;
    }
}

/**
 * Class ContainerTest
 */
class ContainerTest extends TestCase
{
    private function createSimpleCache()
    {
        return new ArrayCache();
    }

    public function testGetInstance() 
    {
        $container = SmartContainer::createDefault($this->createSimpleCache());
        $container['value'] = 1;

        $this->assertEquals(1, $container->get('value'), 'container gets value');
        $this->assertInstanceOf(ExampleClass::class, $container->get(ExampleClass::class));
        $this->assertInstanceOf(AutowireClass::class, ($autowired = $container->get(AutowireClass::class)));
        $this->assertInstanceOf(ExampleClass::class, $autowired->example);

        try {
            $this->assertTrue($container->has(ExtClass::class));
        }
        catch (\Throwable $t) {
            $this->fail("Class not found");
        }
    }

    /**
     * @throws \BigBIT\SmartDI\Exceptions\CannotRedefineException
     */
    public function testPrimitiveAutoWire()
    {
        $container = SmartContainer::createDefault($this->createSimpleCache());
        $container->definePrimitive(PrimitiveClass::class, 'typedPrimitive', 'test')
            ->definePrimitive(PrimitiveClass::class, 'mixedPrimitive', 1.1);

        /** @var PrimitiveClass $instance */
        $instance = $container->get(PrimitiveClass::class);

        $this->assertEquals('test', $instance->typedPrimitive);
        $this->assertEquals(1.1, $instance->mixedPrimitive);

        $container = SmartContainer::createDefault($this->createSimpleCache());
        $container->definePrimitive(PrimitiveClass::class, 'typedPrimitive', function(){ return 'test'; })
            ->definePrimitive(PrimitiveClass::class, 'mixedPrimitive', 1.1);

        /** @var PrimitiveClass $instance */
        $instance = $container->get(PrimitiveClass::class);

        $this->assertEquals('test', $instance->typedPrimitive);
        $this->assertEquals(1.1, $instance->mixedPrimitive);

        // Throwing exceptions
        $exCon = SmartContainer::createDefault($this->createSimpleCache());

        $exCon->definePrimitive(PrimitiveClass::class, 'typedPrimitive', 5.5)
            ->definePrimitive(PrimitiveClass::class, 'mixedPrimitive', new stdClass());

        $tMessage = null;
        try {
            $exCon->get(PrimitiveClass::class);
        } catch (\Throwable $t) {
            $tMessage = $t->getPrevious()->getPrevious()->getMessage();
        }

        $this->assertEquals("Invalid dependency type `double` for `PrimitiveClass` in `typedPrimitive`. It should be `string`.", $tMessage);

        $exCon = SmartContainer::createDefault($this->createSimpleCache());
        $exCon->definePrimitive(PrimitiveClass::class, 'typedPrimitive', function(){ return 5.5; })
            ->definePrimitive(PrimitiveClass::class, 'mixedPrimitive', new stdClass());

        $tMessage = null;
        try {
            $exCon->get(PrimitiveClass::class);
        } catch (\Throwable $t) {
            $tMessage = $t->getPrevious()->getPrevious()->getMessage();
        }

        $this->assertEquals("Invalid dependency type `double` for `PrimitiveClass` in `typedPrimitive`. It should be `string`.", $tMessage);
    }
}
