<?php

use Runalyze\Configuration;
use Runalyze\Util\LocalTime;

/**
 * @group import
 * @group dependsOnOldFactory
 */
class ImporterFiletypeTCXTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ImporterFiletypeTCX
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new ImporterFiletypeTCX;
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
	public function test_notGarmin() {
		$this->object->parseString('<any><xml><file></file></xml></any>');

		$this->assertEquals(0, $this->object->numberOfTrainings());
	}

	/**
	 * Test: standard file
	 * Filename: "Standard.tcx"
	 */
	public function test_generalFile() {
		$this->object->parseFile('../tests/testfiles/tcx/Standard.tcx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals('2011-07-10 11:47', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 6523, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals( 7200 - 8*60 - 21, $this->object->object()->getElapsedTime() );
		$this->assertTrue( $this->object->object()->hasElapsedTime() );

		$this->assertEquals( 22.224, $this->object->object()->getDistance(), '', 0.1);
		$this->assertEquals( 1646, $this->object->object()->getCalories(), '', 10);
		$this->assertEquals( 145, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertEquals( 172, $this->object->object()->getPulseMax(), '', 2);
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayTime() );

		$this->assertEquals( 1, $this->object->object()->Sport()->id() );
		// TODO: missing values

		$this->assertEquals( 5, $this->object->object()->Pauses()->num() );

		foreach (array(
			array(19, 57, 112, 73),
			array(19, 1, 73, 71),
			array(3676, 92, 143, 110),
			array(3720, 11, 125, 126),
			array(6176, 20, 140, 133),
		) as $i => $pause) {
			$this->assertEquals($pause[0], $this->object->object()->Pauses()->at($i)->time());
			$this->assertEquals($pause[1], $this->object->object()->Pauses()->at($i)->duration());
			$this->assertEquals($pause[2], $this->object->object()->Pauses()->at($i)->hrStart());
			$this->assertEquals($pause[3], $this->object->object()->Pauses()->at($i)->hrEnd());
		}
	}

	/**
	 * Test: swimming
	 * Filename: "Swim-without-time_by-Timekiller.tcx"
	 */
	public function test_swimTraining() {
		$this->object->parseFile('../tests/testfiles/tcx/Swim-without-time_by-Timekiller.tcx');

		$this->assertTrue( !$this->object->failed() );

		$this->assertEquals('2012-04-13 13:51', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 2100, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals( 2100, $this->object->object()->getElapsedTime(), '', 30);
		//$this->assertEquals( 5, $this->object->object()->Sport()->id() ); // "Other" is in the file

		$this->assertEquals( "Forerunner 310XT-000", $this->object->object()->getCreatorDetails() );
		$this->assertEquals( "1334325060", $this->object->object()->getActivityId() );
	}

	/**
	 * Test: indoor file
	 * Filename: "Indoor-Training.tcx"
	 */
	public function test_indoorTraining() {
		$this->object->parseFile('../tests/testfiles/tcx/Indoor-Training.tcx');

		$this->assertFalse( $this->object->failed() );

		$this->assertEquals('2012-02-10 16:48', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 7204, $this->object->object()->getTimeInSeconds(), '', 70);
		$this->assertEquals( 7204, $this->object->object()->getElapsedTime() );
		$this->assertEquals( 122, $this->object->object()->getPulseAvg(), '', 2);
		$this->assertEquals( 149, $this->object->object()->getPulseMax(), '', 2);
		//$this->assertEquals( 2, $this->object->object()->Sport()->id() );
	}

	/**
	 * Test: multisport file
	 * Filename: "Multisport.tcx"
	 */
	public function test_multisport() {
		$this->object->parseFile('../tests/testfiles/tcx/Multisport.tcx');

		$this->assertFalse( $this->object->failed() );
		$this->assertTrue( $this->object->hasMultipleTrainings() );
		$this->assertEquals( 3, $this->object->numberOfTrainings() );

		// Activity 1
		$this->assertEquals('2013-04-18 18:14', LocalTime::date('Y-m-d H:i', $this->object->object(0)->getTimestamp()));
		$this->assertEquals(120, $this->object->object(0)->getTimezoneOffset());
		//$this->assertNotEquals( Configuration::General()->runningSport(), $this->object->object(0)->get('sportid') );
		$this->assertEquals( 494, $this->object->object(0)->getTimeInSeconds(), '', 20 );
		$this->assertEquals( 2.355, $this->object->object(0)->getDistance(), '', 0.1 );

		// Activity 2
		$this->assertEquals('2013-04-18 18:24', LocalTime::date('Y-m-d H:i', $this->object->object(1)->getTimestamp()));
		$this->assertEquals(120, $this->object->object(1)->getTimezoneOffset());
		//$this->assertEquals( Configuration::General()->runningSport(), $this->object->object(1)->get('sportid') );
		$this->assertEquals( 3571, $this->object->object(1)->getTimeInSeconds(), '', 30 );
		$this->assertEquals( 11.46, $this->object->object(1)->getDistance(), '', 0.1 );

		// Activity 3
		$this->assertEquals('2013-04-18 19:35', LocalTime::date('Y-m-d H:i', $this->object->object(2)->getTimestamp()));
		$this->assertEquals(120, $this->object->object(2)->getTimezoneOffset());
		//$this->assertNotEquals( Configuration::General()->runningSport(), $this->object->object(2)->get('sportid') );
		$this->assertEquals( 420, $this->object->object(2)->getTimeInSeconds(), '', 10 );
		$this->assertEquals( 2.355, $this->object->object(2)->getDistance(), '', 0.1 );
	}

	/**
	 * Test: dakota file
	 * Filename: "Dakota.tcx"
	 */
	public function test_dakota() {
		$this->object->parseFile('../tests/testfiles/tcx/Dakota.tcx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals('2012-08-19 09:21', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		// Very slow parts (2m in 30s ...), not a good example
		//$this->assertEquals( 1371, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals( 2.34, $this->object->object()->getDistance(), '', 0.1);
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayTime() );
		$this->assertFalse( $this->object->object()->hasArrayPower() );

		//$this->assertNotEquals( 1, $this->object->object()->Sport()->id() );
		// TODO: missing values
	}

	/**
	 * Test: watt extension without namespace (minimized example)
	 * Filename: watt-extension-without-namespace.tcx
	 */
	public function test_wattExtensionWithoutNamespace() {
		$this->object->parseFile('../tests/testfiles/tcx/watt-extension-without-namespace.tcx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals('2013-11-03 14:05', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$this->assertTrue( $this->object->object()->hasArrayPower() );
		$this->assertEquals(
			array(0, 10, 20, 30, 41, 41, 41, 117, 155, 182, 188, 186, 182, 178, 181, 180, 179, 178, 179, 180, 181, 180, 180, 178),
			$this->object->object()->getArrayPower()
		);
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/2012
	 */
	public function testPowerInNS2Extension() {
		$this->object->parseFile('../tests/testfiles/tcx/Power-ns2-extension.tcx');

		$this->assertFalse($this->object->failed());

		$this->assertTrue($this->object->object()->hasArrayPower());
		$this->assertEquals(210, $this->object->object()->getPower());
	}

	/**
	 * Test: DistanceMeters are missing
	 * Filename: "missing-distances.tcx"
	 */
	public function testMissingDistancePoints() {
		$this->object->parseFile('../tests/testfiles/tcx/missing-distances.tcx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals('2010-12-27 15:46', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$DistanceArray = $this->object->object()->getArrayDistance();
		foreach ($DistanceArray as $i => $km) {
			if ($i > 0) {
				$this->assertTrue( $km >= $DistanceArray[$i-1], 'Distance is decreasing');
			}
		}
	}

	/**
	 * Test: Runtastic file - don't look for pauses!
	 * Filename: "Runtastic.tcx"
	 */
	public function testRuntasticFile() {
		$this->object->parseFile('../tests/testfiles/tcx/Runtastic.tcx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals('2015-05-10 16:13', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 61, $this->object->object()->getTimeInSeconds(), '', 5);
		$this->assertEquals( 0.113, $this->object->object()->getDistance(), '', 0.01);
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );

		$this->assertEquals(
			array(23, 25, 27, 31, 32, 35, 37, 39, 45, 50),
			array_slice($this->object->object()->getArrayTime(), 10, 10)
		);
		$this->assertEquals(
			array(0.0, 0.0, 0.0, 0.052, 0.052, 0.052, 0.052, 0.052, 0.071, 0.085),
			array_slice($this->object->object()->getArrayDistance(), 10, 10)
		);
	}

	/**
	 * Test: only route
	 * Filename: "Route-only.tcx"
	 */
	public function testRouteOnly() {
		$this->object->parseFile('../tests/testfiles/tcx/Route-only.tcx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals(0.4, $this->object->object()->getDistance(), '', 0.05);

		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertFalse( $this->object->object()->hasArrayTime() );
	}

	/**
	 * Filename: "Treadmill-doubled-cadence.tcx"
	 * @see https://github.com/Runalyze/Runalyze/issues/1679#issuecomment-169980611
	 */
	public function testDoubledCadence() {
		$this->object->parseFile('../tests/testfiles/tcx/Treadmill-doubled-cadence.tcx');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		$this->assertTrue($this->object->object()->hasArrayTime());
		$this->assertTrue($this->object->object()->hasArrayDistance());
		$this->assertTrue($this->object->object()->hasArrayCadence());
		$this->assertTrue($this->object->object()->hasArrayHeartrate());
		$this->assertFalse($this->object->object()->hasArrayLatitude());
		$this->assertFalse($this->object->object()->hasArrayLongitude());

		$this->assertEquals(67, $this->object->object()->getCadence());
	}

	/**
	 * Filename: "First-point-empty.tcx"
	 * @see https://github.com/Runalyze/Runalyze/issues/1445
	 */
	public function testFirstPointEmpty() {
		$this->object->parseFile('../tests/testfiles/tcx/First-point-empty.tcx');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		$this->assertTrue($this->object->object()->hasArrayLatitude());
		$this->assertTrue($this->object->object()->hasArrayLongitude());

		$this->assertEquals(142, $this->object->object()->getTimeInSeconds());
		$this->assertEquals(0.601, $this->object->object()->getDistance(), '', 0.001);
	}

	/**
	 * Filename: "Wrong-time-zone-by-polar.tcx"
	 * @see https://github.com/Runalyze/Runalyze/issues/1782
	 * @see https://github.com/Runalyze/Runalyze/issues/1779
	 */
	public function testWrongTimeZoneByPolarThatShouldBeFixedWithTimeZoneLookup() {
		$this->object->parseFile('../tests/testfiles/tcx/Wrong-time-zone-by-polar.tcx');

		if (RUNALYZE_TEST_TZ_LOOKUP) {
			$this->assertEquals('2016-04-10 19:40', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
			$this->assertEquals(120, $this->object->object()->getTimezoneOffset());
		} else {
			$this->assertEquals('2016-04-10 18:40', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
			$this->assertEquals(60, $this->object->object()->getTimezoneOffset());
		}

		$this->assertEquals( 1765, $this->object->object()->getTimeInSeconds(), '', 10);
		$this->assertEquals( 3.64, $this->object->object()->getDistance(), '', 0.01);
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
		$this->assertTrue( $this->object->object()->hasArrayCadence() );
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1948
	 */
	public function testNegativePauseFromSigma() {
		$this->object->parseFile('../tests/testfiles/tcx/negative-pause.tcx');

		$pauses = $this->object->object()->Pauses();
		$num = $pauses->num();

		for ($i = 0; $i < $num; ++$i) {
			$this->assertGreaterThan(0, $pauses->at($i)->duration());
		}
	}

}
