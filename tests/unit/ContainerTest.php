<?php

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
    }
}