<?php

use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class ImporterFiletypeSLFTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypeSLF
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new ImporterFiletypeSLF;
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
	public function test_notPWX() {
		$this->object->parseString('<any><xml><file></file></xml></any>');
	}

	/**
	 * Test: standard file
	 * Filename: "Standard.slf" 
	 */
	public function test_withoutDist() {
		$this->object->parseFile('../tests/testfiles/slf/Standard.slf');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals( '29.04.2012 12:58:44', LocalTime::date('d.m.Y H:i:s', $this->object->object()->getTimestamp()) );
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 1257, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 1357, $this->object->object()->getElapsedTime() );
		$this->assertEquals( 5.282, $this->object->object()->getDistance(), '', 0.1);
		$this->assertEquals( 306, $this->object->object()->getCalories(), '', 10);
		$this->assertEquals( 163, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertEquals( 174, $this->object->object()->getPulseMax(), '', 2);
	}

	/**
	 * Test: standard file
	 * Filename: "2012_10_14__13_19_.slf" 
	 */
	public function test_secondFile() {
		$this->object->parseFile('../tests/testfiles/slf/2012_10_14__13_19_.slf');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals( '14.10.2012 13:19:48', LocalTime::date('d.m.Y H:i:s', $this->object->object()->getTimestamp()) );
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 1803, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals( 4.109, $this->object->object()->getDistance(), '', 0.1);
		$this->assertEquals( 243, $this->object->object()->getCalories(), '', 10);
		$this->assertEquals( 120, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertEquals( 135, $this->object->object()->getPulseMax(), '', 2);
	}

	/**
	 * Test: new format
	 * Filename: "DatacenterVersion4-HM.slf" 
	 */
	public function testVersion4File() {
		$this->object->parseFile('../tests/testfiles/slf/DatacenterVersion4-HM.slf');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals( '29.03.2015 11:10:46', LocalTime::date('d.m.Y H:i:s', $this->object->object()->getTimestamp()) );
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 5559, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 20.88, $this->object->object()->getDistance(), '', 0.1);
		$this->assertEquals( 1068, $this->object->object()->getCalories(), '', 10);
		$this->assertEquals( 163, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertEquals( 169, $this->object->object()->getPulseMax(), '', 2);

		$this->assertEquals( 14, count($this->object->object()->Splits()->distancesAsArray()) );
	}
	
	/**
	 * Test: new format
	 * Filename: "DatacenterVersion4-HM.slf" 
	 */
	public function testVersion4WithoutEntriesFile() {
		$this->object->parseFile('../tests/testfiles/slf/slf4-without-entries-.slf');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals( '03.02.2015 10:01:11', LocalTime::date('d.m.Y H:i:s', $this->object->object()->getTimestamp()) );
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 2766, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 7.42, $this->object->object()->getDistance(), '', 0.1);
		$this->assertEquals( 404, $this->object->object()->getCalories(), '', 10);
		$this->assertEquals( 138, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertEquals( 162, $this->object->object()->getPulseMax(), '', 2);

		$this->assertEquals( 0, count($this->object->object()->Splits()->distancesAsArray()) );
	}

}
