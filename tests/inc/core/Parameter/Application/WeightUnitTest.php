<?php

namespace Runalyze\Parameter\Application;

class WeightUnitTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var \Runalyze\Parameter\Application\WeightUnit
	 */
	protected $object;

	protected function setUp()
	{
		$this->object = new WeightUnit;
	}

	public function testKilograms()
	{
		$this->object->set(WeightUnit::KG);

		$this->assertTrue($this->object->isKG());
		$this->assertFalse($this->object->isPounds());
		$this->assertFalse($this->object->isStones());
	}

	public function testPounds()
	{
		$this->object->set(WeightUnit::POUNDS);

		$this->assertFalse($this->object->isKG());
		$this->assertTrue($this->object->isPounds());
		$this->assertFalse($this->object->isStones());
	}

	public function testStones()
	{
		$this->object->set(WeightUnit::STONES);

		$this->assertFalse($this->object->isKG());
		$this->assertFalse($this->object->isPounds());
		$this->assertTrue($this->object->isStones());
	}

}
