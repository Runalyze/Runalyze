<?php

namespace Runalyze\Activity\PaceUnit;

class MinPerKilometerTest extends \PHPUnit_Framework_TestCase
{
	public function testSomePaces()
	{
		$Pace = new MinPerKilometer();

		$this->assertEquals(360, $Pace->rawValue(360));
		$this->assertEquals(300, $Pace->rawValue(300));
	}

	public function testComparison()
	{
		$Pace = new MinPerKilometer();

		$this->assertEquals(+60, $Pace->rawValue($Pace->compare(300, 360)));
		$this->assertEquals(-60, $Pace->rawValue($Pace->compare(360, 300)));
		$this->assertEquals(  0, $Pace->rawValue($Pace->compare(300, 300)));
	}

	public function testFormat()
	{
		$Pace = new MinPerKilometer();

		$this->assertEquals('5:00', $Pace->format(300));
		$this->assertEquals('3:44', $Pace->format(224));
		$this->assertEquals('-:--', $Pace->format(0));
	}
}
