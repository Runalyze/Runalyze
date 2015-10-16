<?php

namespace Runalyze\Activity\PaceUnit;

class MinPer500mTest extends \PHPUnit_Framework_TestCase
{
	public function testSomePaces()
	{
		$Pace = new MinPer500m();

		$this->assertEquals(180, $Pace->rawValue(360));
		$this->assertEquals(150, $Pace->rawValue(300));
	}

	public function testComparison()
	{
		$Pace = new MinPer500m();

		$this->assertEquals(+30, $Pace->rawValue($Pace->compare(300, 360)));
		$this->assertEquals(-30, $Pace->rawValue($Pace->compare(360, 300)));
		$this->assertEquals(  0, $Pace->rawValue($Pace->compare(300, 300)));
	}

	public function testFormat()
	{
		$Pace = new MinPer500m();

		$this->assertEquals('2:30', $Pace->format(300));
		$this->assertEquals('1:52', $Pace->format(224));
		$this->assertEquals('-:--', $Pace->format(0));
	}
}
