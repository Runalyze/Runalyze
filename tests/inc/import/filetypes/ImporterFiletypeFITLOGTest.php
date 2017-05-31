<?php

use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class ImporterFiletypeFITLOGTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypeFITLOG
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new ImporterFiletypeFITLOG;
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
	 */
	public function test_notFITLOG() {
		$this->object->parseString('<any><xml><file></file></xml></any>');

		$this->assertEquals(0, $this->object->numberOfTrainings());
	}

	/**
	 * Test: incorrect xml-file
	 */
	public function test_noTrack() {
		$this->object->parseString('<?xml version="1.0"?>
<FitnessWorkbook xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.zonefivesoftware.com/xmlschemas/FitnessLogbook/v2">
  <AthleteLog>
    <Athlete Name="Michael Pohl"/>
    <Activity StartTime="2013-12-10T00:00:00-03:30">
      <Duration TotalSeconds="2420"/>
      <Distance TotalMeters="10000"/>
      <Calories TotalCal="565"/>
      <Category Name="Laufen"/>
      <Location Name=""/>
    </Activity>
  </AthleteLog>
</FitnessWorkbook>');

		$this->assertFalse( $this->object->failed() );
		$this->assertNotEmpty( $this->object->objects() );
		$this->assertEmpty( $this->object->getErrors() );
		$this->assertEquals('2013-12-10 00:00', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(-210, $this->object->object()->getTimezoneOffset());
	}

	/**
	 * Test: standard file
	 * Filename: "20110411_Laufeinheit_division_by_zero.fitlog"
	 */
	public function test_standard() {
		$this->object->parseFile('../tests/testfiles/sporttracks/20110411_Laufeinheit_division_by_zero.fitlog');

		$this->assertEquals( array(), $this->object->getErrors() );
		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals('2011-04-11 18:52', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 1399, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals( 4.09, $this->object->object()->getDistance(), '', 0.1);
		$this->assertEquals( 361, $this->object->object()->getCalories(), '', 10);
		$this->assertEquals( 161, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertEquals( 176, $this->object->object()->getPulseMax(), '', 2);

		$this->assertFalse( $this->object->object()->Splits()->areEmpty() );
		$this->assertEquals(
			"0.002|0:00-1.000|5:31-1.000|5:40-1.000|5:55-1.000|5:32-0.087|0:39",
			$this->object->object()->Splits()->asString()
		);
	}

	/**
	 * Test: "spinning.fitlog"
	 */
	public function testIndoorSpinning() {
		$this->object->parseFile('../tests/testfiles/sporttracks/spinning.fitlog');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		$this->assertEquals('2015-12-24 12:48', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$this->assertEquals(1803, $this->object->object()->getTimeInSeconds());
		$this->assertEquals(0.0, $this->object->object()->getDistance());
		$this->assertEquals(108, $this->object->object()->getPulseAvg());
		$this->assertEquals(144, $this->object->object()->getPulseMax());

		$this->assertTrue($this->object->object()->hasArrayTime());
		$this->assertTrue($this->object->object()->hasArrayHeartrate());
	}

    public function testWithPauses() {
        $this->object->parseFile('../tests/testfiles/sporttracks/with-pauses.fitlog');

        $this->assertFalse($this->object->hasMultipleTrainings());
        $this->assertFalse($this->object->failed());

        $object = $this->object->object();

        $this->assertEquals('2008-08-01 10:02', LocalTime::date('Y-m-d H:i', $object->getTimestamp()));
        $this->assertEquals(120, $object->getTimezoneOffset());

        $this->assertTrue($object->hasArrayTime());

        $this->assertEquals(4384, $object->getTimeInSeconds());
        $this->assertEquals(4384, $object->getArrayTimeLastPoint());
        $this->assertEquals(4645, $object->getElapsedTime());
        $this->assertEquals(14.67, $object->getDistance());

        $this->assertEquals(2, $object->Pauses()->num());
        $this->assertEquals([
            ['time' => 1408, 'duration' => 33, 'hr-start' => 150, 'hr-end' => 112],
            ['time' => 1771, 'duration' => 228, 'hr-start' => 150, 'hr-end' => 108]
        ], $object->Pauses()->asArray());

        $this->assertFalse($object->Splits()->areEmpty());
        $this->assertEquals(4384, $object->Splits()->totalTime(), '', 30);
        $this->assertEquals(14.67, $object->Splits()->totalDistance(), '', 0.2);
    }

}
