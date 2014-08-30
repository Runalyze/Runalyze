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
		$this->assertEquals( 100, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 113, $this->object->object()->getPulseMax() );
		$this->assertEquals( 21, $this->object->object()->get('temperature') );

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayPace() );
		$this->assertTrue( $this->object->object()->hasArrayTemperature() );
		$this->assertTrue( $this->object->object()->hasArrayTime() );
	}
}