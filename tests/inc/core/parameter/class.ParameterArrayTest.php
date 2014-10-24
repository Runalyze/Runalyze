<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2014-09-15 at 19:05:38.
 */
class ParameterArrayTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ParameterArray
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new ParameterArray(array());
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers ParameterArray::arrayToString
	 * @covers ParameterArray::valueAsString
	 * @covers ParameterArray::setFromString
	 */
	public function testArrayToString() {
		$this->assertEquals(array(), $this->object->value());
		$this->assertEquals('', $this->object->valueAsString());

		$this->object->set(array('a', 'b', 'c'));
		$this->assertEquals(array('a', 'b', 'c'), $this->object->value());
		$this->assertEquals('a,b,c', $this->object->valueAsString());

		$this->object->setFromString('1,2,3');
		$this->assertEquals(array('1', '2', '3'), $this->object->value() );
	}

	/**
	 * @covers ParameterArray::append
	 */
	public function testAppend() {
		$this->assertEquals(array(), $this->object->value());
		$this->assertEquals('', $this->object->valueAsString());

		$this->object->append('one');
		$this->assertEquals(array('one'), $this->object->value());

		$this->object->append('two');
		$this->assertEquals(array('one', 'two'), $this->object->value());
	}

}
