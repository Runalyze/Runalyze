<?php

namespace Runalyze\Data\Weather;

class BeautfortScalaTest extends \PHPUnit_Framework_TestCase {
    
	/**
	 * @var Condition
	 */
	protected $windSpeed;

	protected function setUp() {
		$this->windSpeed = new WindSpeed(15);
	}
	
	public function testString()
	{
	    $Beautfort = new BeautfortScala($this->windSpeed);
	    $this->assertEquals('3 btf', $Beautfort->shortString());
	    $this->assertEquals('3 btf (Gentle breeze)', $Beautfort->longString());
	    $this->assertEquals('3', $Beautfort->get());
	    $this->assertEquals('Beautfort Scala', $Beautfort->label());
	    $this->assertEquals('3', $Beautfort->string(false));
	    $this->assertEquals('3 btf', $Beautfort->string(true));
	    $this->assertEquals('btf', $Beautfort->unit());
	}
	
	public function testSetter() {
	    $object = new BeautfortScala();
	    $object->set(5);
	    $this->assertEquals(5, $object->get());
	    
	    $WindSpeed = new WindSpeed(80);
	    $object->setFromWindSpeed($WindSpeed);
	    $this->assertEquals(9, $object->get());
	}
}