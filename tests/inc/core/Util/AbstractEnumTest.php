<?php

namespace Runalyze\Util;

class AbstractEnum_MockTester extends AbstractEnum
{
	const SUNDAY = 0;
	const MONDAY = 1;
	const TUESDAY = 2;
	const WEDNESDAY = 3;
	const THURSDAY = 4;
	const FRIDAY = 5;
	const SATURDAY = 6;
}

class AbstractEnumTest extends \PHPUnit_Framework_TestCase
{

	public function testEnumeration()
	{
		$this->assertEquals(
			array(
				'SUNDAY' => 0,
				'MONDAY' => 1,
				'TUESDAY' => 2,
				'WEDNESDAY' => 3,
				'THURSDAY' => 4,
				'FRIDAY' => 5,
				'SATURDAY' => 6
			),
			AbstractEnum_MockTester::getEnum()
		);
	}

	public function testValidNames()
	{
		$this->assertFalse(AbstractEnum_MockTester::isValidName('Noday'));
		$this->assertFalse(AbstractEnum_MockTester::isValidName('Sunday', true));

		$this->assertTrue(AbstractEnum_MockTester::isValidName('MONDAY'));
		$this->assertTrue(AbstractEnum_MockTester::isValidName('tuesday'));
	}

	public function testValidValues()
	{
		$this->assertFalse(AbstractEnum_MockTester::isValidValue('0', true));
		$this->assertFalse(AbstractEnum_MockTester::isValidValue(7));

		$this->assertTrue(AbstractEnum_MockTester::isValidValue(2));
	}

}
