<?php

namespace Runalyze\Util;

class AbstractEnumFactoryTrait_MockTester extends AbstractEnum
{
	use AbstractEnumFactoryTrait;

	const FOO = 'foo';
	const FOO_BAR = 'bar';
}

class Foo extends AbstractEnum {}
class FooBar extends AbstractEnum {}

class AbstractEnumFactoryTrait_WrongMockTester
{
    use AbstractEnumFactoryTrait;

    const TEST = 0;
}

class AbstractEnumFactoryTraitTest extends \PHPUnit_Framework_TestCase
{

	public function testFoo()
	{
        $object = AbstractEnumFactoryTrait_MockTester::get(AbstractEnumFactoryTrait_MockTester::FOO);

		$this->assertTrue($object instanceof Foo);
	}

    public function testFooBar()
    {
        $object = AbstractEnumFactoryTrait_MockTester::get(AbstractEnumFactoryTrait_MockTester::FOO_BAR);

        $this->assertTrue($object instanceof FooBar);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidEnum()
    {
        AbstractEnumFactoryTrait_MockTester::get('idontexist');
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testInvalidClass()
    {
        AbstractEnumFactoryTrait_WrongMockTester::get(AbstractEnumFactoryTrait_WrongMockTester::TEST);
    }

}
