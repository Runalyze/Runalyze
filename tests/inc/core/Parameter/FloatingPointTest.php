<?php

namespace Runalyze\Parameter;

class FloatingPointTest extends \PHPUnit_Framework_TestCase
{

	public function testSetFromString()
	{
		$object = new FloatingPoint(3.14);

		$this->assertEquals(3.14, $object->value());
		$this->assertEquals('3.14', $object->valueAsString());

		$object->set(5);
		$this->assertEquals(5, $object->value());
		$this->assertEquals('5', $object->valueAsString());

		$object->setFromString('17');
		$this->assertEquals(17, $object->value() );
	}

    public function testMinMaxOptions()
    {
        $object = new FloatingPoint(1.23, ['min' => 1.0, 'max' => 2.0]);
        $this->assertEquals(1.23, $object->value());

        $object->set(0.12);
        $this->assertEquals(1.0, $object->value());

        $object->set(2.34);
        $this->assertEquals(2.0, $object->value());
    }

    public function testEmptyStringForNotNull()
    {
        $object = new FloatingPoint(3.0, ['null' => false]);
        $object->setFromString('');

        $this->assertNotNull($object->value());
        $this->assertFalse($object->isEmpty());
        $this->assertNotEquals('', $object->valueAsString());
    }

    public function testEmptyStringForNull()
    {
        $object = new FloatingPoint(3.0, ['null' => true]);
        $object->setFromString('');

        $this->assertNull($object->value());
        $this->assertTrue($object->isEmpty());
        $this->assertEquals('', $object->valueAsString());
    }

}
