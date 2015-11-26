<?php

namespace Runalyze\Calculation\Activity;

use Runalyze\Model\Activity;
use Runalyze\Model\Trackdata;

/**
 * @group verticalRatio
 */
class VerticalRatioCalculatorTest extends \PHPUnit_Framework_TestCase
{

	public function testForActivity()
	{
		$this->assertEquals(53.0, VerticalRatioCalculator::forActivity(new Activity\Object(array(
			Activity\Object::VERTICAL_OSCILLATION => 53,
			Activity\Object::STRIDE_LENGTH => 100
		))));
	}

	public function testSingleValue()
	{
		$Calculator = new VerticalRatioCalculator(
			new Trackdata\Object(array(
				Trackdata\Object::VERTICAL_OSCILLATION => array(100),
				Trackdata\Object::STRIDE_LENGTH => array(100)
			))
		);
		$Calculator->calculate();

		$this->assertEquals(100, $Calculator->average());
	}

	public function testSimpleArray()
	{
		$Calculator = new VerticalRatioCalculator(
			new Trackdata\Object(array(
				Trackdata\Object::VERTICAL_OSCILLATION => array(73, 80, 90, 100, 120),
				Trackdata\Object::STRIDE_LENGTH => array(100, 120, 150, 150, 150)
			))
		);
		$Calculator->calculate();

		$this->assertEquals( 69.0, $Calculator->average());
		$this->assertEquals(
			array(73, 67, 60, 67, 80),
			$Calculator->verticalRatioData()
		);
	}

}
