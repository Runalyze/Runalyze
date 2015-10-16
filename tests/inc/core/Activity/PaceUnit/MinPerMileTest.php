<?php

namespace Runalyze\Activity\PaceUnit;

class MinPerMileTest extends \PHPUnit_Framework_TestCase
{
	public function testSomePaces()
	{
		$Pace = new MinPerMile();

		$this->assertEquals(579, $Pace->rawValue(360), '', 0.5);
		$this->assertEquals(483, $Pace->rawValue(300), '', 0.5);
	}

	public function testComparison()
	{
		$Pace = new MinPerMile();

		$this->assertEquals(+97, $Pace->rawValue($Pace->compare(300, 360)), '', 0.5);
		$this->assertEquals(-97, $Pace->rawValue($Pace->compare(360, 300)), '', 0.5);
		$this->assertEquals(  0, $Pace->rawValue($Pace->compare(300, 300)), '', 0.5);
	}

	public function testFormat()
	{
		$Pace = new MinPerMile();

		$this->assertEquals('8:03', $Pace->format(300));
		$this->assertEquals('6:00', $Pace->format(224));
		$this->assertEquals('-:--', $Pace->format(0));
	}
}
