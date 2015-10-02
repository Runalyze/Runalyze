<?php

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
	 */
	public function testEpsonFile() {
		$this->object->parseFile('../tests/testfiles/csv/Epson.csv');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		$this->assertEquals('19.12.2014 16:35', date('d.m.Y H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(4648, $this->object->object()->getTimeInSeconds());

		$this->assertEquals(14, $this->object->object()->getDistance(), '', 0.01);
		$this->assertEquals(1123, $this->object->object()->getCalories());
		$this->assertEquals(168, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertTrue($this->object->object()->hasArrayAltitude());
		$this->assertTrue($this->object->object()->hasArrayDistance());
		$this->assertTrue($this->object->object()->hasArrayHeartrate());
		$this->assertTrue($this->object->object()->hasArrayLatitude());
		$this->assertTrue($this->object->object()->hasArrayLongitude());
		$this->assertTrue($this->object->object()->hasArrayTime());
		$this->assertTrue($this->object->object()->hasArrayCadence());

		$this->assertEquals(14.002, $this->object->object()->getArrayDistanceLastPoint());
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
}