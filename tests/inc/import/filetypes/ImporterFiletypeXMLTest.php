<?php

use Runalyze\Util\LocalTime;

/**
 * @group import
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class ImporterFiletypeXMLTest extends PHPUnit_Framework_TestCase {

	/** @var ImporterFiletypeXML */
	protected $object;

	protected function setUp() {
		$this->object = new ImporterFiletypeXML;
		DB::getInstance()->query('DELETE FROM `'.PREFIX.'training`');
		DB::getInstance()->query('DELETE FROM `'.PREFIX.'equipment`');
	}

	protected function tearDown() {
		DB::getInstance()->query('DELETE FROM `'.PREFIX.'training`');
		DB::getInstance()->query('DELETE FROM `'.PREFIX.'equipment`');
	}

	/**
	 * Test: empty string
	 * @expectedException \Runalyze\Import\Exception\ParserException
	 */
	public function testEmptyString() {
		$this->object->parseString('');
	}

	/**
	 * Test: incorrect xml-file
	 * @expectedException \Runalyze\Import\Exception\ParserException
	 */
	public function test_incorrectString() {
		$this->object->parseString('<any><xml><file></file></xml></any>');
	}

	/**
	 * Test: Polar file
	 * Filename: "Polar.xml"
	 */
	public function test_PolarFile() {
		$this->object->parseFile('../tests/testfiles/xml/Polar.xml');

		$this->assertFalse( $this->object->failed() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );

		$this->assertEquals('2013-03-24 11:33:09', LocalTime::date('Y-m-d H:i:s', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 6.6, $this->object->object()->getDistance() );
		$this->assertEquals( 725, $this->object->object()->getCalories() );
		$this->assertEquals( 48*60 + 49, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 156, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 179, $this->object->object()->getPulseMax() );

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
	}

 	/**
	 * Test: Polar file
	 * Filename: "Polar-with-arrays.xml"
	 */
	public function test_PolarFileWithArrays() {
		$this->object->parseFile('../tests/testfiles/xml/Polar-with-arrays.xml');

		$this->assertFalse( $this->object->failed() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );

		$this->assertEquals('2014-09-07 09:58:10', LocalTime::date('Y-m-d H:i:s', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 20.05, $this->object->object()->getDistance() );
		$this->assertEquals( 2015, $this->object->object()->getCalories() );
		$this->assertEquals( 2*3600 + 9*60 + 0.1, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 157, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 173, $this->object->object()->getPulseMax() );

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );

		$this->assertEquals( array_fill(0, 20, '1.00'), $this->object->object()->Splits()->distancesAsArray() );
		$this->assertEquals( 20.049, $this->object->object()->getArrayDistanceLastPoint(), '', 0.0005 );
	}

 	/**
	 * Test: Polar file
	 * Filename: "Polar-with-arrays.xml"
	 */
	public function test_PolarArraySizesWithAdditionalComma() {
		$this->object->parseFile('../tests/testfiles/xml/Polar-additional-comma.xml');

		$size = count($this->object->object()->getArrayHeartrate());

		$this->assertEquals($size, count($this->object->object()->getArrayAltitude()));
		$this->assertEquals($size, count($this->object->object()->getArrayCadence()));
	}

	/**
	 * Test: Polar file with multiple trainings
	 * Filename: "Multiple-Polar.xml
	 */
	public function test_PolarFile_Multiple() {
		$this->object->parseFile('../tests/testfiles/xml/Multiple-Polar.xml');

		$this->assertFalse( $this->object->failed() );
		$this->assertTrue( $this->object->hasMultipleTrainings() );
		$this->assertEquals( 6, $this->object->numberOfTrainings() );

		$this->assertEquals( LocalTime::mktime(7, 9, 34, 5, 11, 2011), $this->object->object(0)->getTimestamp() );
		$this->assertEquals( 17.1, $this->object->object(0)->getDistance() );

		$this->assertEquals( LocalTime::mktime(17, 31, 19, 5, 11, 2011), $this->object->object(1)->getTimestamp() );
		$this->assertEquals( 16.7, $this->object->object(1)->getDistance() );

		$this->assertEquals( LocalTime::mktime(7, 14, 49, 5, 12, 2011), $this->object->object(2)->getTimestamp() );
		$this->assertEquals( 17.0, $this->object->object(2)->getDistance() );

		$this->assertEquals( LocalTime::mktime(17, 35, 32, 5, 12, 2011), $this->object->object(3)->getTimestamp() );
		$this->assertEquals( 16.5, $this->object->object(3)->getDistance() );

		$this->assertEquals( LocalTime::mktime(7, 12, 15, 5, 13, 2011), $this->object->object(4)->getTimestamp() );
		$this->assertEquals( 17.0, $this->object->object(4)->getDistance() );

		$this->assertEquals( LocalTime::mktime(17, 5, 28, 5, 13, 2011), $this->object->object(5)->getTimestamp() );
		$this->assertEquals( 16.6, $this->object->object(5)->getDistance() );
	}

	/**
	 * Test: RunningAHEAD log
	 * Filename: "RunningAHEAD-Minimal-example.xml"
	 */
	public function test_RunningAHEADFile() {
		$this->object->parseFile('../tests/testfiles/xml/RunningAHEAD-Minimal-example.xml');

		$this->assertFalse( $this->object->failed() );
		$this->assertTrue( $this->object->hasMultipleTrainings() );
		$this->assertEquals( 3, $this->object->numberOfTrainings() );

		// Event 1
		$this->assertEquals( 193, $this->object->object(0)->getPulseAvg() );
		$this->assertEquals( 210, $this->object->object(0)->getPulseMax() );
		$this->assertEquals( 5.0, $this->object->object(0)->getDistance() );
		$this->assertEquals( 1157, $this->object->object(0)->getTimeInSeconds() );
		$this->assertEquals( "Citylauf Telgte", $this->object->object(0)->getRoute() );
		$this->assertEquals( 17, $this->object->object(0)->get('temperature') );
		$this->assertEquals( \Runalyze\Profile\Weather\WeatherConditionProfile::SUNNY, $this->object->object(0)->get('weatherid') );
		$this->assertEquals( "Super organisiert, gute Strecke ...", $this->object->object(0)->getNotes() );

		// Event 2
		$this->assertEquals( 1.0, $this->object->object(1)->getDistance() );
		$this->assertEquals( 2700, $this->object->object(1)->getTimeInSeconds() );

		// Event 3
		$this->assertEquals( 182, $this->object->object(2)->getPulseAvg() );
		$this->assertEquals( 189, $this->object->object(2)->getPulseMax() );
		$this->assertEquals( 4.0, $this->object->object(2)->getDistance() );
		$this->assertEquals( 1000, $this->object->object(2)->getTimeInSeconds() );
		$this->assertEquals( "Bahn Sentruper Hoehe", $this->object->object(2)->getRoute() );
		$this->assertEquals( "4 x 1 km, 400 m Trab", $this->object->object(2)->getTitle() );
		$this->assertEquals( 15, $this->object->object(2)->get('temperature') );
		$this->assertEquals( \Runalyze\Profile\Weather\WeatherConditionProfile::SUNNY, $this->object->object(0)->get('weatherid') );

		$this->assertEquals(
			"1.000|4:10-R0.400|3:00-1.000|4:10-R0.400|3:00-1.000|4:10-R0.400|3:00-1.000|4:10-R1.600|8:00",
			$this->object->object(2)->Splits()->asString()
		);
	}

	/**
	 * Test: RunningAHEAD log
	 * Filename: "RunningAHEAD-Minimal-example-with-equipment.xml"
	 */
	public function test_RunningAHEADFileWithEquipment()
	{
		$this->object->parseFile('../tests/testfiles/xml/RunningAHEAD-Minimal-example-with-equipment.xml');
		foreach ($this->object->objects() as $activity) {
			$activity->insert();
		}

		$PDO = DB::getInstance();
		$ShoeId = $PDO->query('SELECT `id` FROM `'.PREFIX.'equipment` WHERE `name`="New Balance MR 905"')->fetchColumn();
		$this->assertGreaterThan(0, $ShoeId);
		$this->assertEquals(2, $PDO->query('SELECT 1 FROM `'.PREFIX.'activity_equipment` WHERE `equipmentid`='.$ShoeId)->rowCount());
	}

	/**
	 * Test: RunningAHEAD log
	 * Filename: "RunningAHEAD-Minimal-example-with-equipment.xml"
	 */
	public function test_RunningAHEADFileWithExistingEquipment()
	{
		$AccountID = SessionAccountHandler::getId();
		$PDO = DB::getInstance();
		$PDO->exec('INSERT INTO `'.PREFIX.'equipment_type` (`name`, `accountid`) VALUES ("Test", '.$AccountID.')');
		$TypeId = $PDO->lastInsertId();
		$PDO->exec('INSERT INTO `'.PREFIX.'equipment` (`name`, `typeid`, `notes`, `accountid`) VALUES ("New Balance MR 905", '.$TypeId.', "", '.$AccountID.')');
		$ShoeId = $PDO->lastInsertId();

		$this->assertEquals(0, $PDO->query('SELECT 1 FROM `'.PREFIX.'activity_equipment` WHERE `equipmentid`='.$ShoeId)->rowCount());

		$this->object->parseFile('../tests/testfiles/xml/RunningAHEAD-Minimal-example-with-equipment.xml');
		foreach ($this->object->objects() as $activity) {
			$activity->insert();
		}

		$this->assertEquals(2, $PDO->query('SELECT 1 FROM `'.PREFIX.'activity_equipment` WHERE `equipmentid`='.$ShoeId)->rowCount());
	}

	/**
	 * Test: Suunto file
	 * Filename: "Suunto-Ambit-reduced.xml"
	 */
	public function test_SuuntoFile() {
		$this->object->parseFile('../tests/testfiles/xml/Suunto-Ambit-reduced.xml');

		$this->assertFalse( $this->object->failed() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );

		$this->assertEquals( LocalTime::mktime(15, 27, 0, 4, 28, 2013), $this->object->object()->getTimestamp() );
		$this->assertEquals( 0.264, $this->object->object()->getDistance() );
		$this->assertEquals( 107, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 151, $this->object->object()->getElapsedTime() );
		$this->assertEquals( 131, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 143, $this->object->object()->getPulseMax() );
		$this->assertEquals( 461, $this->object->object()->getCalories() );

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayTemperature() );
		$this->assertTrue( $this->object->object()->hasArrayTime() );

		$this->assertEquals( 0.264, $this->object->object()->getArrayDistanceLastPoint() );
	}

	/**
	 * Test: Suunto file
	 * Filename: "Suunto-Ambit-with-laps-reduced.xml"
	 */
	public function test_SuuntoFile_withLaps() {
		$this->object->parseFile('../tests/testfiles/xml/Suunto-Ambit-with-laps-reduced.xml');

		$this->assertFalse( $this->object->failed() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );

		$this->assertEquals( LocalTime::mktime(16, 17, 22, 4, 26, 2014), $this->object->object()->getTimestamp() );
		$this->assertEquals( 5.013, $this->object->object()->getDistance() );
		$this->assertEquals( 1551, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 648, $this->object->object()->getElapsedTime() );
		$this->assertEquals( 111, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 123, $this->object->object()->getPulseMax() );
		$this->assertEquals( 361, $this->object->object()->getCalories() );

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayTemperature() );
		$this->assertTrue( $this->object->object()->hasArrayTime() );

		$this->assertEquals( 0.085, $this->object->object()->getArrayDistanceLastPoint() );

		// New: Cadence && Laps
		$this->assertTrue( $this->object->object()->hasArrayCadence() );
		$this->assertEquals( 86, $this->object->object()->getCadence() );
		$this->assertEquals(
			array(81, 87, 88, 88, 88, 87, 88),
			$this->object->object()->getArrayCadence()
		);

		$this->assertFalse( $this->object->object()->Splits()->areEmpty() );
		$this->assertEquals(
			"1.002|5:33-1.000|5:16",
			$this->object->object()->Splits()->asString()
		);
	}

}
