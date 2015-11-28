<?php

namespace Runalyze\Activity;

class VerticalRatioTest extends \PHPUnit_Framework_TestCase
{

	public function testStaticFunction()
	{
		$this->assertEquals('7.9&nbsp;%', VerticalRatio::format(79, true));
		$this->assertEquals('6.8', VerticalRatio::format(68, false));
	}

	public function testSetter()
	{
		$Ratio = new VerticalRatio(49);

		$this->assertEquals(49, $Ratio->value());
		$this->assertEquals(4.9, $Ratio->inPercent());

		$Ratio->set(50);
		$this->assertEquals(5.0, $Ratio->inPercent());

		$Ratio->setPercent(5.5);
		$this->assertEquals(5.5, $Ratio->inPercent());
		$this->assertEquals(55, $Ratio->value());
	}

}
