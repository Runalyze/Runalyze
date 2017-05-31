<?php

use Runalyze\Util\LocalTime;

/**
 * @group import
 */
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

		$this->assertEquals( '2012-01-18 11:31:40', LocalTime::date('Y-m-d H:i:s', $this->object->object()->getTimestamp()) );
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());
		$this->assertEquals( 9.76, $this->object->object()->getDistance(), '', 0.02 );
		$this->assertEquals( 59*60 + 39.1, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 133, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 144, $this->object->object()->getPulseMax() );
		$this->assertEquals( 83, $this->object->object()->getCadence() );

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );

		$this->assertFalse( $this->object->object()->Splits()->areEmpty() );
		$this->assertEquals(
			'1.009|6:03-1.012|5:59-1.013|5:58-1.017|6:05-1.021|6:44-1.014|6:11-1.015|6:00-1.024|5:56-1.016|6:10',
			$this->object->object()->Splits()->asString()
		);
	}

	public function testFileWithoutPaceData() {
		$this->object->parseFile('../tests/testfiles/hrm/15031101.spinning.hrm');

		$this->assertFalse( $this->object->failed() );

		$this->assertEquals( "11.03.2015 20:18:33", LocalTime::date("d.m.Y H:i:s", $this->object->object()->getTimestamp()) );
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());
		$this->assertEquals( 0.0, $this->object->object()->getDistance() );
		$this->assertEquals( 61*60 + 29.1, $this->object->object()->getTimeInSeconds() );

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
	}

	public function testFileWithoutPaceDataAgain() {
		$this->object->parseFile('../tests/testfiles/hrm/15031801.spinning.hrm');

		$this->assertFalse( $this->object->failed() );

		$this->assertEquals( "18.03.2015 20:15:48", LocalTime::date("d.m.Y H:i:s", $this->object->object()->getTimestamp()) );
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());
		$this->assertEquals( 0.0, $this->object->object()->getDistance() );
		$this->assertEquals( 63*60 + 34.8, $this->object->object()->getTimeInSeconds() );

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
	}

	public function testHRVdata() {
		$this->object->parseFile('../tests/testfiles/hrm/hrv.hrm');

		$this->assertFalse( $this->object->failed() );

		$this->assertEquals( "08.08.2012 18:09:55", LocalTime::date("d.m.Y H:i:s", $this->object->object()->getTimestamp()) );
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());
		$this->assertEquals( 2*60 + 13.3, $this->object->object()->getTimeInSeconds() );

		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
		$this->assertTrue( $this->object->object()->hasArrayHRV() );

		$this->assertEquals( 93, $this->object->object()->getPulseAvg() );
	}
}
