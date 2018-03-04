<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\FileType\Hrm;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class HrmTest extends AbstractActivityParserTestCase
{
    /** @var Hrm */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Hrm();
    }

    public function testSimpleExampleFile()
    {
        $this->parseFile($this->Parser, 'hrm/12011801.hrm');

        $this->assertEquals('2012-01-18 11:31', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(59 * 60 + 39.1, $this->Container->ActivityData->Duration);
        $this->assertEquals(9.76, $this->Container->ActivityData->Distance, '', 0.01);
        $this->assertEquals(133, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(144, $this->Container->ActivityData->MaxHeartRate);
        $this->assertEquals(83, $this->Container->ActivityData->AvgCadence, '', 0.5);

        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);

        $this->checkExpectedRoundData([
            [363, 1.009],
            [359, 1.012],
            [358, 1.015],
            [365, 1.017],
            [404, 1.021],
            [371, 1.014],
            [360, 1.015],
            [356, 1.024],
            [370, 1.018]
        ], 0.5, 0.001);
    }

    public function testFileWithoutPaceData()
    {
        $this->parseFile($this->Parser, 'hrm/15031101.spinning.hrm');

        $this->assertEquals('2015-03-11 20:18', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(61 * 60 + 29.1, $this->Container->ActivityData->Duration);
        $this->assertEquals(0.0, $this->Container->ActivityData->Distance);

        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
    }

    public function testFileWithoutPaceDataAgain()
    {
        $this->parseFile($this->Parser, 'hrm/15031801.spinning.hrm');

        $this->assertEquals('2015-03-18 20:15', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(63 * 60 + 34.8, $this->Container->ActivityData->Duration);
        $this->assertEquals(0.0, $this->Container->ActivityData->Distance);

        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
    }

    public function testFileWithRRData()
    {
        $this->parseFile($this->Parser, 'hrm/hrv.hrm');

        $this->assertEquals('2012-08-08 18:09', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(2 * 60 + 13.3, $this->Container->ActivityData->Duration);
        $this->assertEquals(93, $this->Container->ActivityData->AvgHeartRate, '', 3);

        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->RRIntervals);
    }
}
