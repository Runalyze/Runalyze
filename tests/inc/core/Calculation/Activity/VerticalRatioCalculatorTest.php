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
		$this->assertEquals(5.3, VerticalRatioCalculator::forActivity(new Activity\Object(array(
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

		$this->assertEquals(10, $Calculator->average());
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

		$this->assertEquals( 6.9, $Calculator->average());
		$this->assertEquals(
			array(7.3, 6.7, 6.0, 6.7, 8.0),
			$Calculator->verticalRatioData()
		);
	}

}
