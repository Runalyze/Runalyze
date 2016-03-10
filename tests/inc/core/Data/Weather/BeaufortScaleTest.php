<?php

namespace Runalyze\Data\Weather;

class BeaufortScaleTest extends \PHPUnit_Framework_TestCase
{
	public function testString()
	{
	    $Beaufort = new BeaufortScale(new WindSpeed(15));
	    $this->assertEquals('3 btf', $Beaufort->shortString());
	    $this->assertEquals(3, $Beaufort->value());
	    $this->assertEquals('3', $Beaufort->string(false));
	    $this->assertEquals('3 btf', $Beaufort->string(true));
	    $this->assertEquals('btf', $Beaufort->unit());
	}

	public function testStaticMethods()
	{
		$this->assertEquals('3 btf', BeaufortScale::getShortString(new WindSpeed(15)));
	}

	public function testSetter()
	{
		$object = new BeaufortScale;

	    $this->assertEquals(5, $object->set(5)->value());
	    $this->assertEquals(9, $object->setFromWindSpeed(new WindSpeed(80))->value());
	}

	public function testInvalidValue()
	{
		$this->assertFalse((new BeaufortScale)->set(42)->isValid());
	}

	public function testLimits()
	{
		$object = new BeaufortScale;
		$wind = new WindSpeed;

		$this->assertEquals(0, $object->setFromWindSpeed($wind->set(0))->value());
		$this->assertEquals(0, $object->setFromWindSpeed($wind->set(0.99))->value());
		$this->assertEquals(1, $object->setFromWindSpeed($wind->set(1))->value());
		$this->assertEquals(1, $object->setFromWindSpeed($wind->set(5))->value());
		$this->assertEquals(2, $object->setFromWindSpeed($wind->set(6))->value());
		$this->assertEquals(2, $object->setFromWindSpeed($wind->set(11))->value());
		$this->assertEquals(3, $object->setFromWindSpeed($wind->set(12))->value());
		$this->assertEquals(3, $object->setFromWindSpeed($wind->set(19))->value());
		$this->assertEquals(4, $object->setFromWindSpeed($wind->set(20))->value());
		$this->assertEquals(4, $object->setFromWindSpeed($wind->set(28))->value());
		$this->assertEquals(5, $object->setFromWindSpeed($wind->set(29))->value());
		$this->assertEquals(5, $object->setFromWindSpeed($wind->set(38))->value());
		$this->assertEquals(6, $object->setFromWindSpeed($wind->set(39))->value());
		$this->assertEquals(6, $object->setFromWindSpeed($wind->set(49))->value());
		$this->assertEquals(7, $object->setFromWindSpeed($wind->set(50))->value());
		$this->assertEquals(7, $object->setFromWindSpeed($wind->set(61))->value());
		$this->assertEquals(8, $object->setFromWindSpeed($wind->set(62))->value());
		$this->assertEquals(8, $object->setFromWindSpeed($wind->set(74))->value());
		$this->assertEquals(9, $object->setFromWindSpeed($wind->set(75))->value());
		$this->assertEquals(9, $object->setFromWindSpeed($wind->set(88))->value());
		$this->assertEquals(10, $object->setFromWindSpeed($wind->set(89))->value());
		$this->assertEquals(10, $object->setFromWindSpeed($wind->set(102))->value());
		$this->assertEquals(11, $object->setFromWindSpeed($wind->set(103))->value());
		$this->assertEquals(11, $object->setFromWindSpeed($wind->set(117))->value());
		$this->assertEquals(12, $object->setFromWindSpeed($wind->set(118))->value());
		$this->assertEquals(12, $object->setFromWindSpeed($wind->set(1337))->value());
	}
}