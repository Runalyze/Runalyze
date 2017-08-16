<?php

namespace Runalyze\Calculation\Activity;

use Runalyze\Model;

/**
 * @group dependsOn
 * @group dependsOnOldFactory
 */
class CalculatorTest extends \PHPUnit_Framework_TestCase {

	public function testGeneralFunctionality() {
		$Calculator = new Calculator(new Model\Activity\Entity(array(
			Model\Activity\Entity::DISTANCE => 10,
			Model\Activity\Entity::TIME_IN_SECONDS => 3000,
			Model\Activity\Entity::HR_AVG => 150
		), null, null));

		$this->assertGreaterThan(0, $Calculator->estimateVO2maxByTime());
		$this->assertGreaterThan(0, $Calculator->estimateVO2maxByHeartRate());
		$this->assertGreaterThan(0, $Calculator->estimateVO2maxByHeartRateWithElevation());
		$this->assertGreaterThan(0, $Calculator->calculateTrimp());

		$this->assertGreaterThan($Calculator->estimateVO2maxByTime(), $Calculator->estimateVO2maxByHeartRate());
	}

	public function testEmptyValues() {
		$Calculator = new Calculator(new Model\Activity\Entity(), null, null);

		$this->assertEquals(0, $Calculator->estimateVO2maxByTime());
		$this->assertEquals(0, $Calculator->estimateVO2maxByHeartRate());
		$this->assertEquals(0, $Calculator->estimateVO2maxByHeartRateWithElevation());
		$this->assertEquals(0, $Calculator->calculateTrimp());
	}

	public function testCalculationsWithElevation() {
		$Activity = new Model\Activity\Entity(array(
			Model\Activity\Entity::DISTANCE => 10,
			Model\Activity\Entity::TIME_IN_SECONDS => 3000,
			Model\Activity\Entity::HR_AVG => 150,
			Model\Activity\Entity::ELEVATION => 100
		));

		$CalculatorOnlyActivity = new Calculator($Activity, null, null);
		$CalculatorOnlyElevation = new Calculator($Activity, null, new Model\Route\Entity(array(
			Model\Route\Entity::ELEVATION => 500
		)));
		$CalculatorUpAndDown = new Calculator($Activity, null, new Model\Route\Entity(array(
			Model\Route\Entity::ELEVATION_UP => 500,
			Model\Route\Entity::ELEVATION_DOWN => 100
		)));
		$CalculatorOnlyDown = new Calculator($Activity, null, new Model\Route\Entity(array(
			Model\Route\Entity::ELEVATION => 500,
			Model\Route\Entity::ELEVATION_UP => 0,
			Model\Route\Entity::ELEVATION_DOWN => 500
		)));

		$this->assertGreaterThan(
			$CalculatorOnlyElevation->estimateVO2maxByHeartRateWithElevation(),
			$CalculatorUpAndDown->estimateVO2maxByHeartRateWithElevation()
		);
		$this->assertGreaterThan(
			$CalculatorOnlyDown->estimateVO2maxByHeartRateWithElevation(),
			$CalculatorOnlyElevation->estimateVO2maxByHeartRateWithElevation()
		);
		$this->assertGreaterThan(
			$CalculatorOnlyActivity->estimateVO2maxByHeartRateWithElevation(),
			$CalculatorOnlyElevation->estimateVO2maxByHeartRateWithElevation()
		);
		$this->assertGreaterThan(
			$CalculatorOnlyActivity->estimateVO2maxByHeartRate(),
			$CalculatorOnlyActivity->estimateVO2maxByHeartRateWithElevation()
		);

		$this->assertEquals(
			$CalculatorUpAndDown->estimateVO2maxByHeartRateWithElevationFor(500, 100),
			$CalculatorUpAndDown->estimateVO2maxByHeartRateWithElevation()
		);
		$this->assertEquals(
			$CalculatorOnlyElevation->estimateVO2maxByHeartRateWithElevationFor(500, 500),
			$CalculatorOnlyElevation->estimateVO2maxByHeartRateWithElevation()
		);
		$this->assertEquals(
			$CalculatorOnlyActivity->estimateVO2maxByHeartRateWithElevationFor(100, 100),
			$CalculatorOnlyActivity->estimateVO2maxByHeartRateWithElevation()
		);
	}

	public function testCalculationsWithTrackdata() {
		$Activity = new Model\Activity\Entity(array(
			Model\Activity\Entity::DISTANCE => 10,
			Model\Activity\Entity::TIME_IN_SECONDS => 3000,
			Model\Activity\Entity::HR_AVG => 150
		));

		$CalculatorOnlyActivity = new Calculator($Activity, null, null);
		$CalculatorWithTrackdata = new Calculator($Activity, new Model\Trackdata\Entity(array(
			Model\Trackdata\Entity::TIME => array(1500, 3000),
			Model\Trackdata\Entity::HEARTRATE => array(125, 175)
		)), null);

		$this->assertGreaterThan($CalculatorOnlyActivity->calculateTrimp(), $CalculatorWithTrackdata->calculateTrimp());
	}

	public function testCalculationWithEmptyHeartrateArray() {
		$Activity = new Model\Activity\Entity(array(
			Model\Activity\Entity::DISTANCE => 10,
			Model\Activity\Entity::TIME_IN_SECONDS => 3000,
			Model\Activity\Entity::HR_AVG => 150
		));

		$CalculatorOnlyActivity = new Calculator($Activity, null, null);
		$CalculatorWithTrackdata = new Calculator($Activity, new Model\Trackdata\Entity(array(
			Model\Trackdata\Entity::TIME => array(1500, 3000),
			Model\Trackdata\Entity::HEARTRATE => array(0, 0)
		)), null);

		$this->assertEquals($CalculatorOnlyActivity->calculateTrimp(), $CalculatorWithTrackdata->calculateTrimp());
	}

}
