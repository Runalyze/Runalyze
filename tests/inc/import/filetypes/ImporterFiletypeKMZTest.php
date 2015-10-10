<?php

class ImporterFiletypeKMZTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypeKMZ
	 */
	protected $object;

	protected function setUp() {
		$this->object = new ImporterFiletypeKMZ;
	}

	/**
	 * Test: standard route
	 * Filename: "Baechenstock.kmz" 
	 */
	public function testStandardKMLroute() {
		$this->object->parseFile('../tests/testfiles/kmz/Baechenstock.kmz');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals(12.9, $this->object->object()->getDistance(), '', 0.05);

		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
	}
}
