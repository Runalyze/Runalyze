<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\FileType\Tcx;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class TcxTest extends AbstractActivityParserTestCase
{
    /** @var Tcx */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Tcx();
    }

    public function testStandardFile()
    {
        $this->parseFile($this->Parser, 'tcx/Standard.tcx');

        $this->assertEquals('2011-07-10 11:47', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(6523, $this->Container->ActivityData->Duration, '', 30);
        $this->assertEquals(7200 - 8 * 60 - 21, $this->Container->ActivityData->ElapsedTime, '', 5);

        $this->assertEquals(22.224, $this->Container->ActivityData->Distance, '', 0.1);
        $this->assertEquals(1646, $this->Container->ActivityData->EnergyConsumption, '', 10);
        $this->assertEquals(145, $this->Container->ActivityData->AvgHeartRate, '', 2);
        $this->assertEquals(172, $this->Container->ActivityData->MaxHeartRate, '', 2);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);

        $this->checkExpectedPauseData([
            [19, 57, 112, 73],
            [19, 1, 73, 71],
            [3676, 92, 143, 110],
            [3720, 11, 125, 126],
            [6176, 20, 140, 133]
        ]);
    }

    public function testSwimTraining()
    {
        $this->parseFile($this->Parser, 'tcx/Swim-without-time_by-Timekiller.tcx');

        $this->assertEquals('2012-04-13 13:51', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(2100, $this->Container->ActivityData->Duration, '', 30);
        $this->assertEquals(2100, $this->Container->ActivityData->ElapsedTime, '', 30);

        $this->assertEquals("Other", $this->Container->Metadata->getSportName());
        $this->assertEquals("Forerunner 310XT-000", $this->Container->Metadata->getCreatorDetails());
    }

    public function testIndoorActivity()
    {
        $this->parseFile($this->Parser, 'tcx/Indoor-Training.tcx');

        $this->assertEquals('2012-02-10 16:48', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(7204, $this->Container->ActivityData->Duration, '', 70);
        $this->assertEquals(7204, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(122, $this->Container->ActivityData->AvgHeartRate, '', 2);
        $this->assertEquals(149, $this->Container->ActivityData->MaxHeartRate, '', 2);
    }

    public function testMultiSport()
    {
        $this->parseFile($this->Parser, 'tcx/Multisport.tcx');

        $this->assertTrue(is_array($this->Container));
        $this->assertEquals(3, count($this->Container));

        $this->assertEquals('2013-04-18 18:14', LocalTime::date('Y-m-d H:i', $this->Container[0]->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container[0]->Metadata->getTimezoneOffset());
        $this->assertEquals(494, $this->Container[0]->ActivityData->Duration, '', 20);
        $this->assertEquals(2.355, $this->Container[0]->ActivityData->Distance, '', 0.1);

        $this->assertEquals('2013-04-18 18:24', LocalTime::date('Y-m-d H:i', $this->Container[1]->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container[1]->Metadata->getTimezoneOffset());
        $this->assertEquals(3571, $this->Container[1]->ActivityData->Duration, '', 30);
        $this->assertEquals(11.46, $this->Container[1]->ActivityData->Distance, '', 0.1);

        $this->assertEquals('2013-04-18 19:35', LocalTime::date('Y-m-d H:i', $this->Container[2]->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container[2]->Metadata->getTimezoneOffset());
        $this->assertEquals(420, $this->Container[2]->ActivityData->Duration, '', 10);
        $this->assertEquals(2.355, $this->Container[2]->ActivityData->Distance, '', 0.1);
    }

    public function testFileFromDakota()
    {
        $this->parseFile($this->Parser, 'tcx/Dakota.tcx');

        $this->assertEquals('2012-08-19 09:21', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(2.34, $this->Container->ActivityData->Distance, '', 0.1);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertEmpty($this->Container->ContinuousData->Power);
    }

    public function test_wattExtensionWithoutNamespace()
    {
        $this->parseFile($this->Parser, 'tcx/watt-extension-without-namespace.tcx');

        $this->assertEquals('2013-11-03 14:05', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(
            [0, 10, 20, 30, 41, 41, 41, 117, 155, 182, 188, 186, 182, 178, 181, 180, 179, 178, 179, 180, 181, 180, 180, 178],
            $this->Container->ContinuousData->Power
       );
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/2012
     */
    public function testPowerInNS2Extension()
    {
        $this->parseFile($this->Parser, 'tcx/Power-ns2-extension.tcx');

        $this->assertNotEmpty($this->Container->ContinuousData->Power);
        $this->assertEquals(210, round($this->Container->ActivityData->AvgPower));
    }

    public function testMissingDistancePoints()
    {
        $this->parseFile($this->Parser, 'tcx/missing-distances.tcx');

        $this->assertEquals('2010-12-27 15:46', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        foreach ($this->Container->ContinuousData->Distance as $i => $km) {
            if ($i > 0) {
                $this->assertTrue($km >= $this->Container->ContinuousData->Distance[$i-1], 'Distance is decreasing');
            }
        }
    }

    public function testFileFromRuntastic()
    {
        $this->parseFile($this->Parser, 'tcx/Runtastic.tcx');

        $this->assertEquals('2015-05-10 16:13', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(61, $this->Container->ActivityData->Duration, '', 5);
        $this->assertEquals(0.113, $this->Container->ActivityData->Distance, '', 0.01);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);

        $this->assertEquals(
            [23, 25, 27, 31, 32, 35, 37, 39, 45, 50],
            array_slice($this->Container->ContinuousData->Time, 10, 10)
       );
        $this->assertEquals(
            [0.0, 0.0, 0.0, 0.052, 0.052, 0.052, 0.052, 0.052, 0.071, 0.085],
            array_slice($this->Container->ContinuousData->Distance, 10, 10)
       );
    }

    public function testRouteOnly()
    {
        $this->parseFile($this->Parser, 'tcx/Route-only.tcx');

        $this->assertEquals(0.4, $this->Container->ActivityData->Distance, '', 0.05);

        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1679#issuecomment-169980611
     */
    public function testDoubledCadence()
    {
        $this->parseFile($this->Parser, 'tcx/Treadmill-doubled-cadence.tcx');

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);

        $this->assertEquals(67, $this->Container->ActivityData->AvgCadence, '', 0.5);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1445
     */
    public function testFirstPointEmpty()
    {
        $this->parseFile($this->Parser, 'tcx/First-point-empty.tcx');

        $this->assertEquals(142, $this->Container->ActivityData->Duration);
        $this->assertEquals(0.601, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1782
     * @see https://github.com/Runalyze/Runalyze/issues/1779
     */
    public function testWrongTimeZoneByPolarThatShouldBeFixedWithTimeZoneLookup()
    {
        $this->parseFile($this->Parser, 'tcx/Wrong-time-zone-by-polar.tcx');

        if (RUNALYZE_TEST_TZ_LOOKUP) {
            $this->assertEquals('2016-04-10 19:40', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
            $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());
        } else {
            $this->assertEquals('2016-04-10 18:40', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
            $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());
        }

        $this->assertEquals(1765, $this->Container->ActivityData->Duration, '', 10);
        $this->assertEquals(3.64, $this->Container->ActivityData->Distance, '', 0.01);

        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1948
     */
    public function testNegativePauseFromSigma()
    {
        $this->parseFile($this->Parser, 'tcx/negative-pause.tcx');

        foreach ($this->Container->Pauses as $pause) {
            $this->assertGreaterThan(0, $pause->getDuration());
        }
    }
}
