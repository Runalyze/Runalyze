<?php
class ImporterFiletypeSMLTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypeXML
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new ImporterFiletypeSML;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() { }

	/**
	 * Test: empty string
	 */
	public function testEmptyString() {
		$this->object->parseString('');

		$this->assertTrue( $this->object->failed() );
		$this->assertEmpty( $this->object->objects() );
		$this->assertNotEmpty( $this->object->getErrors() );
	}

	/**
	 * Test: incorrect xml-file 
	 */
	public function test_incorrectString() {
		$this->object->parseString('<any><xml><file></file></xml></any>');

		$this->assertTrue( $this->object->failed() );
		$this->assertEmpty( $this->object->objects() );
		$this->assertNotEmpty( $this->object->getErrors() );
	}

	/**
	 * Test: Suunto file
	 * Filename: "Suunto-Ambit-reduced.sml" 
	 */
	public function test_SuuntoFile() {
		$this->object->parseFile('../tests/testfiles/sml/Suunto-Ambit-reduced.sml');

		$this->assertFalse( $this->object->failed() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );

		$this->assertEquals( mktime(10, 15, 36, 8, 22, 2014), $this->object->object()->getTimestamp() );
		$this->assertEquals( 0.100, $this->object->object()->getDistance() );
		$this->assertEquals( 3773, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 39, $this->object->object()->getElapsedTime() );
		$this->assertEquals( 91, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 113, $this->object->object()->getPulseMax() );
		$this->assertEquals( 752, $this->object->object()->getCalories() );

		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayTemperature() );
		$this->assertEquals(
			array(21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21),
			$this->object->object()->getArrayTemperature()
		);

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
		$this->assertEquals(
			array(71.0, 74.0, 74.0, 76.0, 79.0, 90.0, 95.0, 99.0, 103.0, 108.0, 111.0, 113.0),
			$this->object->object()->getArrayHeartrate()
		);

		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertEquals(
			array(285, 284, 285, 285, 285, 285, 285, 286, 286, 287, 287, 288),
			$this->object->object()->getArrayAltitude()
		);

		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertEquals(
			array(0.0, 0.0, 0.0, 0.0, 0.012, 0.024, 0.037, 0.048, 0.061, 0.074, 0.087, 0.1),
			$this->object->object()->getArrayDistance()
		);

		$this->assertTrue( $this->object->object()->hasArrayTime() );
		$this->assertEquals(
			array(1, 2, 3, 4, 5, 9, 13, 17, 21, 26, 31, 36),
			$this->object->object()->getArrayTime()
		);

		$this->assertEquals( 0.100, $this->object->object()->getArrayDistanceLastPoint() );
	}

	/**
	 * Test: Suunto file indoor
	 * Filename: "Suunto-Ambit-Indoor-reduced.sml" 
	 */
	public function test_SuuntoFileIndoor() {
		$this->object->parseFile('../tests/testfiles/sml/Suunto-Ambit-Indoor-reduced.sml');

		$this->assertFalse( $this->object->failed() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );

		$this->assertEquals( mktime(15, 15, 9, 10, 15, 2014), $this->object->object()->getTimestamp() );
		$this->assertEquals( 6.06, $this->object->object()->getDistance() );
		$this->assertEquals( 3964, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 79, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 81, $this->object->object()->getPulseMax() );
		$this->assertEquals( 624, $this->object->object()->getCalories() );

		$this->assertFalse( $this->object->object()->hasArrayLatitude() );
		$this->assertFalse( $this->object->object()->hasArrayLongitude() );

		$this->assertTrue( $this->object->object()->hasArrayTemperature() );
		$this->assertEquals(
			array(27, 27, 27, 27, 27, 27, 27, 27, 27, 27),
			$this->object->object()->getArrayTemperature()
		);

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
		$this->assertEquals(
			array(79.0, 78.0, 78.0, 78.0, 79.0, 79.0, 79.0, 79.0, 80.0, 81.0),
			$this->object->object()->getArrayHeartrate()
		);

		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertEquals(
			array(0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.001, 0.002, 0.003, 0.005),
			$this->object->object()->getArrayDistance()
		);

		$this->assertTrue( $this->object->object()->hasArrayTime() );
		$this->assertEquals(
			array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
			$this->object->object()->getArrayTime()
		);

		$this->assertEquals( 0.005, $this->object->object()->getArrayDistanceLastPoint() );
	}

	/**
	 * Test: Suunto Ambit3 with only RR data
	 * Filename: "Suunto-Ambit3-only-RR-reduced.sml" 
	 */
	public function testSuuntoAmbit3withOnlyRRdata() {
		$this->object->parseFile('../tests/testfiles/sml/Suunto-Ambit3-only-RR-reduced.sml');

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );

		// Only the first samples up to 0.106 km are available
		// Header data does not match anymore
		$this->assertEquals( 117, $this->object->object()->getPulseAvg(), '', 0 );
		$this->assertEquals( 131, $this->object->object()->getPulseMax(), '', 0 );

		$this->assertTrue( $this->object->object()->hasArrayHRV() );
	}
}