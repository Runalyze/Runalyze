<?php

use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class ImporterFiletypeKMLTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypeKML
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new ImporterFiletypeKML;
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
	public function test_notKML() {
		$this->object->parseString('<any><xml><file></file></xml></any>');
	}

	/**
	 * Test: standard file
	 * Filename: "TomTom.kml"
	 */
	public function test_standardFileFromTomTom() {
		$this->object->parseFile('../tests/testfiles/kml/TomTom.kml');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		// assume that '...Z' time strings are converted to Europe/Berlin (see bootstrap.php)
		$this->assertEquals('2013-09-08 10:34', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 3637, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 3788, $this->object->object()->getElapsedTime() );
		$this->assertEquals( 12.816, $this->object->object()->getDistance(), '', 0.1);
		$this->assertEquals( 808, $this->object->object()->getCalories(), '', 10);

		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayTime() );
	}

	/**
	 * Test: standard file without explicit distance
	 * Filename: "TomTom-without-distance-extension.kml"
	 */
	public function testFileFromTomTomWithoutDistance() {
		$this->object->parseFile('../tests/testfiles/kml/TomTom-without-distance-extension.kml');

		// assume that '...Z' time strings are converted to Europe/Berlin (see bootstrap.php)
		$this->assertEquals('2015-03-15 07:29', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals( 5*60*60 + 51*60 + 51, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 12.816, $this->object->object()->getDistance(), '', 0.1);

		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayTime() );
	}

	/**
	 * Test: standard route
	 * Filename: "Route-only.kml"
	 */
	public function testStandardKMLroute() {
		$this->object->parseFile('../tests/testfiles/kml/Route-only.kml');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals(0.4, $this->object->object()->getDistance(), '', 0.05);

		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );

		$this->assertFalse( $this->object->object()->hasArrayTime() );
	}

	/**
	 * Filename: "Route-only-with-zeros.kml"
	 */
	public function testStandardKMLrouteWithZeros() {
		$this->object->parseFile('../tests/testfiles/kml/Route-only-with-zeros.kml');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		$this->assertEquals(0.4, $this->object->object()->getDistance(), '', 0.05);
		$this->assertEquals(11, count($this->object->object()->getArrayDistance()));
	}

	/**
	 * Test: multi line route without altitude
	 * Filename: "multi-line-without-altitude.kml"
	 */
	public function testMultiLineWithoutAltitude() {
		$this->object->parseFile('../tests/testfiles/kml/multi-line-without-altitude.kml');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals(2.25, $this->object->object()->getDistance(), '', 0.05);

		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );

		$this->assertFalse( $this->object->object()->hasArrayAltitude() );
		$this->assertFalse( $this->object->object()->hasArrayTime() );
	}

	/**
	 * Filename: "Suunto-Spartan-Ultra.kml"
	 */
	public function testSpartanUltraWithTemperature() {
		$this->object->parseFile('../tests/testfiles/kml/Suunto-Spartan-Ultra.kml');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		$this->assertEquals(0.098, $this->object->object()->getDistance(), '', 0.001);

		$this->assertTrue($this->object->object()->hasArrayDistance());
		$this->assertTrue($this->object->object()->hasArrayLatitude());
		$this->assertTrue($this->object->object()->hasArrayLongitude());
		$this->assertTrue($this->object->object()->hasArrayAltitude());
		$this->assertTrue($this->object->object()->hasArrayTime());
		$this->assertTrue($this->object->object()->hasArrayCadence());
		$this->assertTrue($this->object->object()->hasArrayTemperature());
	}
}
