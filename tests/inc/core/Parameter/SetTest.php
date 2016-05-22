<?php

namespace Runalyze\Parameter;

class ParameterSetTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \Runalyze\Parameter\Set
	 */
	protected $object;

	protected function setUp() {
		$this->object = new Set(array());
	}

	public function testArrayToString() {
		$this->assertEquals(array(), $this->object->value());
		$this->assertEquals('', $this->object->valueAsString());

		$this->object->set(array('a', 'b', 'c'));
		$this->assertEquals(array('a', 'b', 'c'), $this->object->value());
		$this->assertEquals('a,b,c', $this->object->valueAsString());

		$this->object->setFromString('1,2,3');
		$this->assertEquals(array('1', '2', '3'), $this->object->value() );
	}

	public function testAppend() {
		$this->assertEquals(array(), $this->object->value());
		$this->assertEquals('', $this->object->valueAsString());

		$this->object->append('one');
		$this->assertEquals(array('one'), $this->object->value());

		$this->object->append('two');
		$this->assertEquals(array('one', 'two'), $this->object->value());
	}

	public function testMaxLength() {
		$maxString = str_repeat('a', \Runalyze\Parameter::MAX_LENGTH);

		$this->object->append($maxString);
		$this->object->setFromString($this->object->valueAsString());
		$this->assertEquals([$maxString], $this->object->value());

		$this->object->append('foo');
		$this->object->setFromString($this->object->valueAsString());
		$this->assertEquals(['foo'], $this->object->value());
	}

}
