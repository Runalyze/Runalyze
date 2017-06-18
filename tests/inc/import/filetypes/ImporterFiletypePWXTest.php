<?php

use Runalyze\Util\LocalTime;

/**
 * @group import
 * @group dependsOnOldFactory
 */
class ImporterFiletypePWXTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypePWX
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new ImporterFiletypePWX;
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
	public function test_notPWX() {
		$this->object->parseString('<any><xml><file></file></xml></any>');

		$this->assertTrue( $this->object->failed() );
		$this->assertEmpty( $this->object->objects() );
		$this->assertNotEmpty( $this->object->getErrors() );
	}

	/**
	 * Test: standard file
	 * Filename: "without-dist.pwx"
	 */
	public function test_withoutDist() {
		$this->object->parseFile('../tests/testfiles/pwx/without-dist.pwx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals('2009-02-10 06:15', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));

		if (RUNALYZE_TEST_TZ_LOOKUP) {
			$this->assertEquals(-420, $this->object->object()->getTimezoneOffset());
		} else {
			$this->assertEquals(null, $this->object->object()->getTimezoneOffset());
		}

		$this->assertEquals( 1646, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals( 4.891, $this->object->object()->getDistance(), '', 0.1);

		$this->assertEquals('Stuart', $this->object->object()->getTitle());
		$this->assertEquals("Apple, iPhone (SERIAL_NUMBER)", $this->object->object()->getCreatorDetails());

		$this->assertTrue($this->object->object()->Splits()->areEmpty());
	}

	/**
	 * Test: standard file
	 * Filename: "with-dist.pwx"
	 */
	public function test_withDist() {
		$this->object->parseFile('../tests/testfiles/pwx/with-dist.pwx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals('2008-11-16 11:40', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));

		if (RUNALYZE_TEST_TZ_LOOKUP) {
			$this->assertEquals(-420, $this->object->object()->getTimezoneOffset());
		} else {
			$this->assertEquals(null, $this->object->object()->getTimezoneOffset());
		}

		$this->assertEquals( 6978, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals( 16.049, $this->object->object()->getDistance(), '', 0.1);

		$this->assertEquals('Blue Sky trail with Dan and Ian', $this->object->object()->getTitle());
		$this->assertEquals("Garmin, Edge 205/305 (EDGE305 Software Version 3.20)", $this->object->object()->getCreatorDetails());

		$this->assertEquals(4, count($this->object->object()->Splits()->asArray()));
	}

	/**
	 * Test: standard file
	 * Filename: "with-dist-and-hr.pwx"
	 */
	public function test_withDistAndHr() {
		$this->object->parseFile('../tests/testfiles/pwx/with-dist-and-hr.pwx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals( 13539, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals( 89.535, $this->object->object()->getDistance(), '', 0.1);
		$this->assertEquals( 146, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertEquals( 174, $this->object->object()->getPulseMax(), '', 2);

		$this->assertEquals(1, count($this->object->object()->Splits()->asArray()));
	}

	/**
	 * Test: standard file
	 * Filename: "with-dist-and-hr.pwx"
	 */
	public function test_withPower() {
		$this->object->parseFile('../tests/testfiles/pwx/with-power.pwx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertTrue( $this->object->object()->hasArrayPower() );
		$this->assertTrue( $this->object->object()->getPower() > 0 );

		$this->assertEquals(18, count($this->object->object()->Splits()->asArray()));
	}

	/**
	 * Test: with intervals
	 * Filename: "intervals.pwx"
	 */
	public function testIntervals() {
		$this->object->parseFile('../tests/testfiles/pwx/intervals.pwx');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		$this->assertEquals(4813 - 289, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals(15.00, $this->object->object()->getDistance(), '', 0.1);
		$this->assertEquals('05.08.2015', LocalTime::date('d.m.Y', $this->object->object()->getTimestamp()));

		$this->assertEquals(9, count($this->object->object()->Splits()->asArray()));

		$Pauses = $this->object->object()->Pauses();

		$this->assertEquals(2, $Pauses->num());

		foreach ([
			 [635, 3, 155, 154],
			 [640, 286, 154, 0]
		 ] as $i => $pause) {
			$this->assertEquals($pause[0], $Pauses->at($i)->time());
			$this->assertEquals($pause[1], $Pauses->at($i)->duration());
			$this->assertEquals($pause[2], $Pauses->at($i)->hrStart());
			$this->assertEquals($pause[3], $Pauses->at($i)->hrEnd());
		}
	}

}
