<?php

use BigBIT\example\ExtClass;
use BigBIT\SmartDI\SmartContainer;

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
    /**
     * @throws \BigBIT\SmartDI\Exceptions\CannotResolveException
     * @throws \BigBIT\SmartDI\Exceptions\DefinitionNotFoundException
     */
    public function testGetInstance() {
        $container = new SmartContainer();
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
     * @throws \BigBIT\SmartDI\Exceptions\CannotResolveException
     * @throws \BigBIT\SmartDI\Exceptions\DefinitionNotFoundException
     */
    public function testPrimitiveAutoWire()
    {
        $container = new SmartContainer();
        $container->setPrimitive(PrimitiveClass::class, 'typedPrimitive', 'test')
            ->setPrimitive(PrimitiveClass::class, 'mixedPrimitive', 1.1);

        /** @var PrimitiveClass $instance */
        $instance = $container->get(PrimitiveClass::class);

        $this->assertEquals('test', $instance->typedPrimitive);
        $this->assertEquals(1.1, $instance->mixedPrimitive);

        $container = new SmartContainer();
        $container->setPrimitive(PrimitiveClass::class, 'typedPrimitive', function(){ return 'test'; })
            ->setPrimitive(PrimitiveClass::class, 'mixedPrimitive', 1.1);

        /** @var PrimitiveClass $instance */
        $instance = $container->get(PrimitiveClass::class);

        $this->assertEquals('test', $instance->typedPrimitive);
        $this->assertEquals(1.1, $instance->mixedPrimitive);

        // Throwing exceptions
        $exCon = new SmartContainer();

        $exCon->setPrimitive(PrimitiveClass::class, 'typedPrimitive', 5.5)
            ->setPrimitive(PrimitiveClass::class, 'mixedPrimitive', new stdClass());

        $tMessage = null;
        try {
            $exCon->get(PrimitiveClass::class);
        } catch (\Throwable $t) {
            $tMessage = $t->getPrevious()->getMessage();
        }

        $this->assertEquals("Invalid dependency type `double` for `PrimitiveClass` in `typedPrimitive`. It should be `string`.", $tMessage);

        $exCon->setPrimitive(PrimitiveClass::class, 'typedPrimitive', function(){ return 5.5; })
            ->setPrimitive(PrimitiveClass::class, 'mixedPrimitive', new stdClass());

        $tMessage = null;
        try {
            $exCon->get(PrimitiveClass::class);
        } catch (\Throwable $t) {
            $tMessage = $t->getPrevious()->getMessage();
        }

        $this->assertEquals("Invalid dependency type `double` for `PrimitiveClass` in `typedPrimitive`. It should be `string`.", $tMessage);
    }
}
