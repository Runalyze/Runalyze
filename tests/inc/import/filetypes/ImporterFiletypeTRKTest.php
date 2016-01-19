<?php

class ImporterFiletypeTRKTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypeTRK
	 */
	protected $object;

	protected function setUp() {
		$this->object = new ImporterFiletypeTRK;
	}

	public function testSimpleExampleFile() {
		$this->object->parseFile('../tests/testfiles/trk/minimal-example.trk');

		$this->assertFalse( $this->object->failed() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );

		$this->assertEquals( '06-04-2015 15:37:38', date('d-m-Y H:i:s', $this->object->object()->getTimestamp()) );
		$this->assertNotEquals( 0, $this->object->object()->getDistance() );
		$this->assertEquals( 6, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 107, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 108, $this->object->object()->getPulseMax() );

		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertEquals(
			array(108, 108, 107, 107, 107, 107, 107),
			$this->object->object()->getArrayHeartrate()
		);
		$this->assertEquals(
			array( 15,  15,  15,  16,  16,  16,  16),
			$this->object->object()->getArrayTemperature()
		);
		$this->assertEquals(
			array(189, 189, 189, 189, 189, 189, 189),
			$this->object->object()->getArrayAltitude()
		);
	}

	public function testFileWithPause() {
		$this->object->parseFile('../tests/testfiles/trk/with-pause.trk');

		$this->assertFalse( $this->object->failed() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );

		$this->assertEquals( '12-04-2015 11:23:00', date('d-m-Y H:i:s', $this->object->object()->getTimestamp()) );
		$this->assertNotEquals( 0, $this->object->object()->getDistance() );
		$this->assertEquals( 20, $this->object->object()->getTimeInSeconds() );

		$this->assertFalse( $this->object->object()->hasArrayHeartrate() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertEquals(
			array(
				0, 1, 2, 3, 4, 5, 6, 7, 8,
				9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20
			),
			$this->object->object()->getArrayTime()
		);
	}
}