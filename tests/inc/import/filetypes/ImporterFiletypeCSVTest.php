<?php

use Runalyze\Util\LocalTime;

class ImporterFiletypeCSVTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypeCSV
	 */
	protected $object;

	protected function setUp() {
		$this->object = new ImporterFiletypeCSV;
	}

	/**
	 * Test: standard file
	 * Filename: "Epson.csv"
	 * @group epson
	 */
	public function testEpsonFile() {
		$this->object->parseFile('../tests/testfiles/csv/Epson.csv');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		$this->assertEquals('19.12.2014 16:35', LocalTime::date('d.m.Y H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());
		$this->assertEquals(4648, $this->object->object()->getTimeInSeconds());

		$this->assertEquals(14, $this->object->object()->getDistance(), '', 0.01);
		$this->assertEquals(1123, $this->object->object()->getCalories());
		$this->assertEquals(171, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertTrue($this->object->object()->hasArrayAltitude());
		$this->assertTrue($this->object->object()->hasArrayDistance());
		$this->assertTrue($this->object->object()->hasArrayHeartrate());
		$this->assertTrue($this->object->object()->hasArrayLatitude());
		$this->assertTrue($this->object->object()->hasArrayLongitude());
		$this->assertTrue($this->object->object()->hasArrayTime());
		$this->assertTrue($this->object->object()->hasArrayCadence());

		$this->assertEquals(14.002, $this->object->object()->getArrayDistanceLastPoint(), '', 0.01);
		$this->assertEquals(4648, $this->object->object()->getArrayTimeLastPoint());
		$this->assertEquals(array(82), array_slice($this->object->object()->getArrayHeartrate(), 0, 1));
		$this->assertEquals(array(61), array_slice($this->object->object()->getArrayCadence(), 0, 1));
		$this->assertEquals(array(238), array_slice($this->object->object()->getArrayAltitude(), 0, 1));
		$this->assertEquals(array(49.878523), array_slice($this->object->object()->getArrayLatitude(), 0, 1));
		$this->assertEquals(array(10.906175), array_slice($this->object->object()->getArrayLongitude(), 0, 1));

		$this->assertEquals(
			array(327, 314, 308, 311, 306, 316, 331, 397, 339, 351, 374, 332, 327, 305),
			$this->object->object()->Splits()->timesAsArray()
		);
	}

	/**
	 * Test: standard file
	 * Filename: "Epson.csv"
	 * @group epson
	 * @see https://github.com/Runalyze/Runalyze/issues/1575
	 */
	public function testEpsonFileWithDifferentArraySizes() {
		$this->object->parseFile('../tests/testfiles/csv/Epson-different-array-sizes.csv');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		$sizeTimeData = count($this->object->object()->getArrayTime());

		$this->assertEquals($sizeTimeData, count($this->object->object()->getArrayHeartrate()), 'Heartrate array has wrong size.');
		$this->assertEquals($sizeTimeData, count($this->object->object()->getArrayCadence()), 'Cadence array has wrong size.');
		$this->assertEquals($sizeTimeData, count($this->object->object()->getArrayDistance()), 'Distance array has wrong size.');

		$this->assertEquals($sizeTimeData, count($this->object->object()->getArrayAltitude()), 'Altitude array has wrong size.');
		$this->assertEquals($sizeTimeData, count($this->object->object()->getArrayLongitude()), 'Latitude array has wrong size.');
		$this->assertEquals($sizeTimeData, count($this->object->object()->getArrayLongitude()), 'Longitude array has wrong size.');
	}

	/**
	 * Original file was cutted after 500 data points
	 *
	 * @group wahoo
	 * @see https://github.com/Runalyze/Runalyze/issues/1965
	 */
	public function testWahooIPhoneApp() {
		$this->object->parseFile('../tests/testfiles/csv/Wahoo.csv');

		$this->assertFalse($this->object->failed());
		$this->assertFalse($this->object->hasMultipleTrainings());

		$activity = $this->object->object();
		$splits = $activity->Splits();

		$this->assertEquals('07.10.2016 17:24', LocalTime::date('d.m.Y H:i', $activity->getTimestamp()));
		$this->assertEquals(11, count($splits->asArray()));
		$this->assertEquals(4618, $splits->totalTime(), '', 5);
		$this->assertEquals(10.016, $splits->totalDistance(), '', 0.01);

		$this->assertTrue($activity->hasArrayDistance());
		$this->assertTrue($activity->hasPositionData());
		$this->assertTrue($activity->hasArrayAltitude());
		$this->assertTrue($activity->hasArrayHeartrate());
		$this->assertTrue($activity->hasArrayCadence());
		$this->assertTrue($activity->hasArrayVerticalOscillation());
		$this->assertTrue($activity->hasArrayGroundContact());
		$this->assertFalse($activity->Pauses()->isEmpty());

		$this->assertEquals(490, $activity->getTimeInSeconds());
		$this->assertEquals(1.086, $activity->getDistance(), '', 0.001);
		$this->assertEquals(147, $activity->getPulseAvg());
		$this->assertEquals(82, $activity->getCadence());
		$this->assertEquals(75, $activity->getVerticalOscillation());
		$this->assertEquals(300, $activity->getGroundContactTime());
	}
}
