<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\FileType\Gpx;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class GpxTest extends AbstractActivityParserTestCase
{
    /** @var Gpx */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Gpx();
    }

    public function testStandardFile()
    {
        $this->parseFile($this->Parser, 'gpx/standard.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        // assume that '...Z' time strings are converted to Europe/Berlin (see bootstrap.php)
        $this->assertEquals('2013-02-04 21:38', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(2295, $this->Container->ActivityData->Duration, '', 15);
        $this->assertEquals(2306, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(5.993, $this->Container->ActivityData->Distance, '', 0.1);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertEmpty($this->Container->ContinuousData->Temperature);
    }

    /**
     * @return string
     */
    protected function getExampleXmlForExtensions()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
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
</gpx>';
    }

    public function testExtensions()
    {
        $this->parseFileContent($this->Parser, $this->getExampleXmlForExtensions());

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        // Assume '...Z' is converted to Europe/Berlin
        $this->assertEquals('2013-02-04 21:38', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(10, $this->Container->ActivityData->Duration);
        $this->assertEquals(90, $this->Container->ActivityData->AvgCadence);

        $this->assertEquals([0, 10], $this->Container->ContinuousData->Time);
        $this->assertEquals([275, 280], $this->Container->ContinuousData->Altitude);
        $this->assertEquals([125, 120], $this->Container->ContinuousData->HeartRate);
        $this->assertEquals([90, 90], $this->Container->ContinuousData->Cadence);
        $this->assertEquals([28, 26], $this->Container->ContinuousData->Temperature);
    }

    public function testFileFromSpoQ()
    {
        $this->parseFile($this->Parser, 'gpx/SpoQ.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        // assume that '...Z' time strings are converted to Europe/Berlin (see bootstrap.php)
        $this->assertEquals('2013-09-29 11:36', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(112, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(132, $this->Container->ActivityData->MaxHeartRate);
    }

    public function testAnotherFileFromSpoQ()
    {
        $this->parseFile($this->Parser, 'gpx/SpoQ-2.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        // assume that '...Z' time strings are converted to Europe/Berlin (see bootstrap.php)
        $this->assertEquals('2014-07-01 10:18', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(106, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(128, $this->Container->ActivityData->MaxHeartRate);
    }

    public function testFileFromNavRun500()
    {
        $this->parseFile($this->Parser, 'gpx/NavRun500.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        // assume that '...Z' time strings are converted to Europe/Berlin (see bootstrap.php)
        $this->assertEquals('2015-05-25 14:40', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        // Original start: 2015-05-25T11:05:01Z
        // New start: 2015-05-25T12:40:01Z (i.e. -1:35:00)
        // New end: 2015-05-25T13:53:01Z
        // Original Pauses:
        // 01:40:39 - 01:45:04 or 12:45:40 - 12:49:33
        // 02:17:09 - 02:24:26 or 13:22:10 - 13:29:18
        // 02:28:57 - 02:46:56 or 13:33:58 - 13:51:53
        // - sum of (large) pauses = 29:41 or 29:00

        $this->assertEquals(1 * 3600 + 13 * 60 - 29 * 60, $this->Container->ActivityData->Duration, '', 10);

        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);

        $this->checkExpectedPauseData([
            [339, 233],
            [2296, 428],
            [2576, 1075]
        ]);
    }

    public function testStandardGpxRoute()
    {
        $this->parseFile($this->Parser, 'gpx/Route-only.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        $this->assertEquals(0.4, $this->Container->ActivityData->Distance, '', 0.05);

        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1367
     */
    public function testStravaExport()
    {
        $this->parseFile($this->Parser, 'gpx/strava-export.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        // Assume '...Z' is converted to Europe/Berlin
        $this->assertEquals('2015-12-01 21:11', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(87, $this->Container->ActivityData->AvgCadence, '', 0.5);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1936
     */
    public function testThatMissingHeartRatePointsAreFilled()
    {
        $this->parseFile($this->Parser, 'gpx/hr-not-always-there.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        $this->assertEquals(137, $this->Container->ActivityData->AvgHeartRate, '', 0.5);

        $this->assertNotContains(0, $this->Container->ContinuousData->HeartRate);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1946
     */
    public function testThatExtensionsInNs3FromGarminAreParsed()
    {
        $this->parseFile($this->Parser, 'gpx/garmin-ns3-extension.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->Temperature);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/2064
     */
    public function testMixedExtensionsFromMovescount()
    {
        $this->parseFile($this->Parser, 'gpx/Movescount-mixed-extensions.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/2194
     */
    public function testBreakBetweenTrackSegments()
    {
        $this->parseFile($this->Parser, 'gpx/break-between-trkseg.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        // Assume '...Z' is converted to Europe/Berlin
        $this->assertEquals('2010-04-02 10:26', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(5 * 3600 + 17 * 60 + 21, $this->Container->ActivityData->Duration, '', 10);

        $this->checkExpectedPauseData([
            [13402, 3780]
        ]);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/2255
     */
    public function testNegativeTimeStepsFromRunkeeper()
    {
        $this->parseFile($this->Parser, 'gpx/runkeeper-negative-time-step-at-start.gpx');

        $this->assertInstanceOf(ActivityDataContainer::class, $this->Container);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);

        $this->assertEquals(3329, $this->Container->ActivityData->Duration, '', 10);
        $this->assertEquals(3329, $this->Container->ActivityData->ElapsedTime, '', 10);
        $this->assertEquals(151, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
    }
}
