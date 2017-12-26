<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Activity\FileType\Csv;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class CsvTest extends AbstractActivityParserTestCase
{
    /** @var Csv */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Csv();
    }

    /**
     * @group importerEpson
     */
    public function testStandardEpsonFile()
    {
        $this->parseFile($this->Parser, 'csv/Epson.csv');

        $this->assertEquals('19-12-2014 16:35', LocalTime::date('d-m-Y H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(4648, $this->Container->ActivityData->Duration);
        $this->assertEquals(14.0, $this->Container->ActivityData->Distance, '', 0.01);
        $this->assertEquals(1123, $this->Container->ActivityData->EnergyConsumption);
        $this->assertEquals(169, $this->Container->ActivityData->AvgHeartRate, '', 2);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);

        $lastIndex = count($this->Container->ContinuousData->Time) - 1;

        $this->assertEquals(14.002, $this->Container->ContinuousData->Distance[$lastIndex], '', 0.01);
        $this->assertEquals(4648, $this->Container->ContinuousData->Time[$lastIndex]);
        $this->assertEquals(82, $this->Container->ContinuousData->HeartRate[0]);
        $this->assertEquals(61, $this->Container->ContinuousData->Cadence[0]);
        $this->assertEquals(238, $this->Container->ContinuousData->Altitude[0]);
        $this->assertEquals(49.878523, $this->Container->ContinuousData->Latitude[0]);
        $this->assertEquals(10.906175, $this->Container->ContinuousData->Longitude[0]);

        $this->assertEquals(
            [327, 314, 308, 311, 306, 316, 331, 397, 339, 351, 374, 332, 327, 305],
            array_map(function (Round $v) {
                return $v->getDuration();
            }, $this->Container->Rounds->getElements())
        );
    }

    /**
     * Original file was cutted after 500 data points
     *
     * @group importerWahoo
     * @see https://github.com/Runalyze/Runalyze/issues/1965
     */
    public function testStandardWahooFile()
    {
        $this->parseFile($this->Parser, 'csv/Wahoo.csv');

        $this->assertEquals('07-10-2016 17:24', LocalTime::date('d-m-Y H:i', $this->Container->Metadata->getTimestamp()));

        $this->assertEquals(490, $this->Container->ActivityData->Duration);
        $this->assertEquals(1.086, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(147, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(82, $this->Container->ActivityData->AvgCadence, '', 0.5);
        $this->assertEquals(300, $this->Container->ActivityData->AvgGroundContactTime, '', 0.5);
        $this->assertEquals(75, $this->Container->ActivityData->AvgVerticalOscillation, '', 0.5);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->GroundContactTime);
        $this->assertNotEmpty($this->Container->ContinuousData->VerticalOscillation);

        $this->assertEquals(11, count($this->Container->Rounds));
        $this->assertEquals(4618, $this->Container->Rounds->getTotalDuration(), '', 5);
        $this->assertEquals(10.016, $this->Container->Rounds->getTotalDistance(), '', 0.01);

        $this->assertGreaterThan(0, $this->Container->Pauses->count());
    }
}
