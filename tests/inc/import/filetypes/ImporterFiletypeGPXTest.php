<?php

use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class ImporterFiletypeGPXTest extends PHPUnit_Framework_TestCase
{
	/** @var ImporterFiletypeGPX */
	protected $object;

	protected function setUp()
    {
		$this->object = new ImporterFiletypeGPX;
	}

	/**
	 * @expectedException \Runalyze\Import\Exception\ParserException
	 */
	public function testEmptyString()
    {
		$this->object->parseString('');
	}

	public function testInvalidString()
    {
		$this->object->parseString('<any><xml><file></file></xml></any>');

		$this->assertEquals(0, $this->object->numberOfTrainings());
	}

	public function testSimpleExample()
    {
		$this->object->parseFile('../tests/testfiles/gpx/standard.gpx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		// Assume '...Z' is converted to Europe/Berlin
		$this->assertEquals('2013-02-04 21:38', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 2295, $this->object->object()->getTimeInSeconds(), '', 30);
		$this->assertEquals( 2306, $this->object->object()->getElapsedTime() );
		$this->assertEquals( 5.993, $this->object->object()->getDistance(), '', 0.1);

		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasPositionData() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayTime() );
	}

	public function testExtensions()
    {
		$this->object->parseString('<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:gpxdata="http://www.cluetrust.com/XML/GPXDATA/1/0">
	<trk>
		<trkseg>
			<trkpt lat="50.7749991026" lon="6.1125158798">
				<ele>275</ele>
				<time>2013-02-04T20:38:10Z</time>
				<extensions>
					<gpxdata:hr>125</gpxdata:hr>
					<gpxdata:temp>28</gpxdata:temp>
					<gpxdata:cadence>90</gpxdata:cadence>
				</extensions>
			</trkpt>
			<trkpt lat="50.7749992026" lon="6.1125158798">
				<ele>280</ele>
				<time>2013-02-04T20:38:20Z</time>
				<extensions>
					<gpxdata:hr>120</gpxdata:hr>
					<gpxdata:temp>26</gpxdata:temp>
					<gpxdata:cadence>90</gpxdata:cadence>
				</extensions>
			</trkpt>
		</trkseg>
	</trk>
</gpx>');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		// Assume '...Z' is converted to Europe/Berlin
		$this->assertEquals('2013-02-04 21:38', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 10, $this->object->object()->getTimeInSeconds() );
		$this->assertEquals( 90, $this->object->object()->getCadence() );

		$this->assertEquals( array(0, 10), $this->object->object()->getArrayTime() );
		$this->assertEquals( array(275,280), $this->object->object()->getArrayAltitude() );
		$this->assertEquals( array(125,120), $this->object->object()->getArrayHeartrate() );
		$this->assertEquals( array(90, 90), $this->object->object()->getArrayCadence() );
		$this->assertEquals( array(28, 26), $this->object->object()->getArrayTemperature() );
	}

	public function test_SpoQ()
    {
		$this->object->parseFile('../tests/testfiles/gpx/SpoQ.gpx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		// Assume '...Z' is converted to Europe/Berlin
		$this->assertEquals('2013-09-29 11:36', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 112, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 132, $this->object->object()->getPulseMax() );
		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
	}

	public function test_SpoQ2()
    {
		$this->object->parseFile('../tests/testfiles/gpx/SpoQ-2.gpx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		// Assume '...Z' is converted to Europe/Berlin
		$this->assertEquals('2014-07-01 10:18', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 106, $this->object->object()->getPulseAvg() );
		$this->assertEquals( 128, $this->object->object()->getPulseMax() );
		$this->assertTrue( $this->object->object()->hasArrayHeartrate() );
	}

	public function testNavRun500()
    {
		$this->object->parseFile('../tests/testfiles/gpx/NavRun500.gpx');

		// Original start: 2015-05-25T11:05:01Z
		// New start: 2015-05-25T12:40:01Z (i.e. -1:35:00)
		// New end: 2015-05-25T13:53:01Z
		// Original Pauses:
		// 01:40:39 - 01:45:04 or 12:45:40 - 12:49:33
		// 02:17:09 - 02:24:26 or 13:22:10 - 13:29:18
		// 02:28:57 - 02:46:56 or 13:33:58 - 13:51:53
		// - sum of (large) pauses = 29:41 or 29:00

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		// Assume '...Z' is converted to Europe/Berlin
		$this->assertEquals('2015-05-25 14:40', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(120, $this->object->object()->getTimezoneOffset());

		$this->assertEquals( 1*60*60 + 13*60 - 29*60, $this->object->object()->getTimeInSeconds(), '', 10 );
		$this->assertTrue( $this->object->object()->hasArrayAltitude() );

		$this->assertEquals( 3, $this->object->object()->Pauses()->num() );

		foreach (array(
			array(339, 233),
			array(2296, 428),
			array(2576, 1075),
		) as $i => $pause) {
			$this->assertEquals($pause[0], $this->object->object()->Pauses()->at($i)->time());
			$this->assertEquals($pause[1], $this->object->object()->Pauses()->at($i)->duration());
		}
	}

	public function testStandardGPXroute()
    {
		$this->object->parseFile('../tests/testfiles/gpx/Route-only.gpx');

		$this->assertFalse( $this->object->hasMultipleTrainings() );
		$this->assertFalse( $this->object->failed() );

		$this->assertEquals(0.4, $this->object->object()->getDistance(), '', 0.05);

		$this->assertTrue( $this->object->object()->hasArrayAltitude() );
		$this->assertTrue( $this->object->object()->hasArrayDistance() );
		$this->assertTrue( $this->object->object()->hasArrayLatitude() );
		$this->assertTrue( $this->object->object()->hasArrayLongitude() );
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1367
	 */
	public function testStravaExport()
    {
		$this->object->parseFile('../tests/testfiles/gpx/strava-export.gpx');

		$this->assertFalse($this->object->hasMultipleTrainings());
		$this->assertFalse($this->object->failed());

		// Assume '...Z' is converted to Europe/Berlin
		$this->assertEquals('2015-12-01 21:11', LocalTime::date('Y-m-d H:i', $this->object->object()->getTimestamp()));
		$this->assertEquals(60, $this->object->object()->getTimezoneOffset());

		$this->assertTrue($this->object->object()->hasArrayCadence());
		$this->assertEquals(87, $this->object->object()->getCadence());
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1936
	 */
	public function testThatMissingHeartRatePointsAreFilled()
    {
		$this->object->parseFile('../tests/testfiles/gpx/hr-not-always-there.gpx');

		$this->assertTrue($this->object->object()->hasArrayHeartrate());
		$this->assertEquals(137, $this->object->object()->getPulseAvg());

		$this->assertNotContains(0, $this->object->object()->getArrayHeartrate());
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1946
	 */
	public function testThatExtensionsInNs3FromGarminAreParsed()
    {
		$this->object->parseFile('../tests/testfiles/gpx/garmin-ns3-extension.gpx');

		$this->assertTrue($this->object->object()->hasArrayHeartrate());
		$this->assertTrue($this->object->object()->hasArrayCadence());
		$this->assertTrue($this->object->object()->hasArrayTemperature());
	}

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/2064
     */
    public function testMixedExtensionsFromMovescount()
    {
        $this->object->parseFile('../tests/testfiles/gpx/Movescount-mixed-extensions.gpx');

        $this->assertTrue($this->object->object()->hasArrayDistance());
        $this->assertTrue($this->object->object()->hasArrayAltitude());
        $this->assertTrue($this->object->object()->hasArrayHeartrate());
        $this->assertTrue($this->object->object()->hasArrayCadence());
    }
}
