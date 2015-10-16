<?php

namespace Runalyze\Activity\PaceUnit;

class MinPer500yTest extends \PHPUnit_Framework_TestCase
{
	public function testSomePaces()
	{
		$Pace = new MinPer500y();

		$this->assertEquals(165, $Pace->rawValue(360), '', 0.5);
		$this->assertEquals(137, $Pace->rawValue(300), '', 0.5);
	}

	public function testComparison()
	{
		$Pace = new MinPer500y();

		$this->assertEquals(+27.4, $Pace->rawValue($Pace->compare(300, 360)), '', 0.1);
		$this->assertEquals(-27.4, $Pace->rawValue($Pace->compare(360, 300)), '', 0.1);
		$this->assertEquals(  0, $Pace->rawValue($Pace->compare(300, 300)));
	}

	public function testFormat()
	{
		$Pace = new MinPer500y();

		$this->assertEquals('2:17', $Pace->format(300));
		$this->assertEquals('1:42', $Pace->format(224));
		$this->assertEquals('-:--', $Pace->format(0));
	}
}
