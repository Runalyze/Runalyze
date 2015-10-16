<?php

namespace Runalyze\Activity\PaceUnit;

class KmPerHourTest extends \PHPUnit_Framework_TestCase
{
	public function testSomePaces()
	{
		$Pace = new KmPerHour();

		$this->assertEquals(10, $Pace->rawValue(360));
		$this->assertEquals(12, $Pace->rawValue(300));
	}

	public function testComparison()
	{
		$Pace = new KmPerHour();

		$this->assertEquals(+2, $Pace->rawValue($Pace->compare(300, 360)));
		$this->assertEquals(-2, $Pace->rawValue($Pace->compare(360, 300)));
		$this->assertEquals( 0, $Pace->rawValue($Pace->compare(300, 300)));
	}

	public function testFormat()
	{
		AbstractDecimalUnit::$DecimalSeparator = ',';
		AbstractDecimalUnit::$Decimals = 1;

		$Pace = new KmPerHour();

		$this->assertEquals('12,0', $Pace->format(300));
		$this->assertEquals('16,1', $Pace->format(224));
		$this->assertEquals('0,0', $Pace->format(0));
	}
}
