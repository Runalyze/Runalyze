<?php

class ImporterFiletypeHRMTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypeHRM
	 */
	protected $object;

	protected function setUp() {
		$this->object = new ImporterFiletypeHRM;
	}

	/**
	 * Test: 12011801.hrm
	 */
	public function testSimpleExampleFile() {
		$this->object->parseFile('../tests/testfiles/hrm/12011801.hrm');

		$this->assertFalse( $this->object->failed() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );

		$this->assertEquals( mktime(11, 31, 40, 1, 18, 2012), $this->object->object()->getTimestamp() );
		$this->assertEquals( 0, $this->object->object()->getDistance() );
		$this->assertEquals( 59*60 + 39.1, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 133, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 144, $this->object->object()->getPulseMax() );
		$this->assertEquals( 83, $this->object->object()->getCadence() );

		$this->assertTrue( $this->object->object()->hasArrayPace() );
		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );

		$this->assertFalse( $this->object->object()->Splits()->areEmpty() );
	}
}