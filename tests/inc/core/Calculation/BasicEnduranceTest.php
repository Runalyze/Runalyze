<?php

namespace Runalyze\Calculation;

use Runalyze\Configuration;
use DB;
use Runalyze\Util\LocalTime;

class BasicEnduranceTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var BasicEndurance
	 */
	protected $object;

	protected function setUp() {
		$this->object = new BasicEndurance;
		DB::getInstance()->exec('DELETE FROM `runalyze_training`');
	}

	protected function tearDown() {
		DB::getInstance()->exec('DELETE FROM `runalyze_training`');
	}

	public function testSetterAndGetter() {
		$this->object->setEffectiveVO2max(50);
		$this->object->setDaysToRecognizeForLongjogs(30);
		$this->object->setDaysToRecognizeForWeekKilometer(60);
		$this->object->setMinimalDaysToRecognizeForWeekKilometer(30);
		$this->object->setMinimalDistanceForLongjogs(10);

		$this->assertEquals(50, $this->object->getUsedEffectiveVO2max());
		$this->assertEquals(30, $this->object->getDaysToRecognizeForLongjogs());
		$this->assertEquals(60, $this->object->getDaysToRecognizeForWeekKilometer());
		$this->assertEquals(30, $this->object->getMinimalDaysToRecognizeForWeekKilometer());
		$this->assertEquals(10, $this->object->getMinimalDistanceForLongjogs());
	}

	public function testSetterAndGetterForPercentages() {
		$this->object->setPercentageForLongjogs(0.4);
		$this->assertEquals(0.4, $this->object->getPercentageForLongjogs());
		$this->assertEquals(0.6, $this->object->getPercentageForWeekKilometer());

		$this->object->setPercentageForWeekKilometer(0.1);
		$this->assertEquals(0.9, $this->object->getPercentageForLongjogs());
		$this->assertEquals(0.1, $this->object->getPercentageForWeekKilometer());

		$this->object->setPercentageForLongjogs(15);
		$this->assertEquals(1, $this->object->getPercentageForLongjogs());
		$this->assertEquals(0, $this->object->getPercentageForWeekKilometer());

		$this->object->setPercentageForLongjogs(-15);
		$this->assertEquals(1, $this->object->getPercentageForLongjogs());
		$this->assertEquals(0, $this->object->getPercentageForWeekKilometer());
	}

	public function testThatMinimalVO2maxIsUsed() {
		$this->object->setEffectiveVO2max(20);
		$this->assertEquals(25, $this->object->getUsedEffectiveVO2max());
	}

	public function testTargets() {
		$this->object->setMinimalDistanceForLongjogs(10);

		$this->object->setEffectiveVO2max(30);
		$this->assertEquals(47.5, $this->object->getTargetWeekKm(), '', 1);
		$this->assertEquals(24, $this->object->getRealTargetLongjogKmPerWeek(), '', 1);

		$this->object->setEffectiveVO2max(40);
		$this->assertEquals(66, $this->object->getTargetWeekKm(), '', 1);
		$this->assertEquals(27.5, $this->object->getRealTargetLongjogKmPerWeek(), '', 1);

		$this->object->setEffectiveVO2max(50);
		$this->assertEquals(85, $this->object->getTargetWeekKm(), '', 1);
		$this->assertEquals(30, $this->object->getRealTargetLongjogKmPerWeek(), '', 1);

		$this->object->setEffectiveVO2max(60);
		$this->assertEquals(104, $this->object->getTargetWeekKm(), '', 1);
		$this->assertEquals(32.5, $this->object->getRealTargetLongjogKmPerWeek(), '', 1);

		$this->object->setEffectiveVO2max(70);
		$this->assertEquals(124, $this->object->getTargetWeekKm(), '', 1);
		$this->assertEquals(34.5, $this->object->getRealTargetLongjogKmPerWeek(), '', 1);

		$this->object->setEffectiveVO2max(80);
		$this->assertEquals(145, $this->object->getTargetWeekKm(), '', 1);
		$this->assertEquals(36, $this->object->getRealTargetLongjogKmPerWeek(), '', 1);
	}

	public function testForVO2max30() {
		$this->object->setEffectiveVO2max(30);
		$this->object->setDaysToRecognizeForLongjogs(7);
		$this->object->setDaysToRecognizeForWeekKilometer(7);
		$this->object->setMinimalDaysToRecognizeForWeekKilometer(7);
		$this->object->setMinimalDistanceForLongjogs(10);
		$this->object->setPercentageForLongjogs(0.33);
		$this->object->setPercentageForWeekKilometer(0.67);

		DB::getInstance()->insert('training', array('sportid', 'time', 'distance', 's'), array(Configuration::General()->runningSport(), LocalTime::fromServerTime(time())->getTimestamp() - 1*DAY_IN_S, 15, 2));
		DB::getInstance()->insert('training', array('sportid', 'time', 'distance', 's'), array(Configuration::General()->runningSport(), LocalTime::fromServerTime(time())->getTimestamp() - 3*DAY_IN_S, 15, 2));
		DB::getInstance()->insert('training', array('sportid', 'time', 'distance', 's'), array(Configuration::General()->runningSport(), LocalTime::fromServerTime(time())->getTimestamp() - 8*DAY_IN_S, 42, 2));

		$Results = $this->object->asArray();
		$this->assertEquals(30, $Results['weekkm-result']);
		$this->assertEquals(0.36, $Results['longjog-result'], '', 0.01);
		$this->assertEquals(0.63, $Results['weekkm-percentage'], '', 0.01);
		$this->assertEquals(0.36, $Results['longjog-percentage'], '', 0.01);
		$this->assertEquals(54, $Results['percentage'], '', 0.01);
		$this->assertEquals(54, $this->object->value());
		$this->assertEquals('54 &#37;', $this->object->valueInPercent());

		$this->object->setPercentageForLongjogs(1);
		$this->assertEquals(36, $this->object->value());

		$this->object->setDaysToRecognizeForLongjogs(14);
		$this->assertEquals(240, $this->object->value());

		// Attention: depens on START_TIME
		$this->object->setMinimalDaysToRecognizeForWeekKilometer(70);
		$this->object->setDaysToRecognizeForWeekKilometer(70);
		$this->object->setPercentageForLongjogs(0.01);
		$this->assertEquals(17, $this->object->value());
	}

}
