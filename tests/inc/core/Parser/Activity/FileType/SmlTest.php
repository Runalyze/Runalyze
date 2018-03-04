<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\FileType\Sml;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class SmlTest extends AbstractActivityParserTestCase
{
    /** @var Sml */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Sml();
    }

    public function testReducedAmbitFile()
    {
        $this->parseFile($this->Parser, 'sml/Suunto-Ambit-reduced.sml');

        $this->assertEquals('2014-08-22 10:15', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));

        $this->assertEquals(0.100, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(3773, $this->Container->ActivityData->Duration);
        $this->assertEquals(752, $this->Container->ActivityData->EnergyConsumption);
        $this->assertEquals(100, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(113, $this->Container->ActivityData->MaxHeartRate);

        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);

        $this->assertEquals(
            [1, 2, 3, 4, 5, 9, 13, 17, 21, 26, 31, 36],
            $this->Container->ContinuousData->Time
        );

        $this->assertEquals(
            [0.0, 0.0, 0.0, 0.0, 0.012, 0.024, 0.037, 0.048, 0.061, 0.074, 0.087, 0.1],
            $this->Container->ContinuousData->Distance
        );

        $this->assertEquals(
            [285, 284, 285, 285, 285, 285, 285, 286, 286, 287, 287, 288],
            $this->Container->ContinuousData->Altitude
        );

        $this->assertEquals(
            [71.0, 74.0, 74.0, 76.0, 79.0, 90.0, 95.0, 99.0, 103.0, 108.0, 111.0, 113.0],
            $this->Container->ContinuousData->HeartRate
        );

        $this->assertEquals(
            [21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21],
            $this->Container->ContinuousData->Temperature
        );
    }

    public function testIndoorAmbitFile()
    {
        $this->parseFile($this->Parser, 'sml/Suunto-Ambit-Indoor-reduced.sml');

        $this->assertEquals('2014-10-15 15:15', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));

        $this->assertEquals(6.060, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(3964, $this->Container->ActivityData->Duration);
        $this->assertEquals(624, $this->Container->ActivityData->EnergyConsumption);
        $this->assertEquals(79, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(81, $this->Container->ActivityData->MaxHeartRate);

        $this->assertEquals(3.6, $this->Container->FitDetails->TrainingEffect);

        $this->assertEmpty($this->Container->ContinuousData->Latitude);
        $this->assertEmpty($this->Container->ContinuousData->Longitude);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Temperature);
    }

    public function testAmbit3FileWithOnlyRRIntervals()
    {
        $this->parseFile($this->Parser, 'sml/Suunto-Ambit3-only-RR-reduced.sml');

        $this->assertNotEmpty($this->Container->RRIntervals);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);

        $this->assertEquals(118, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(131, $this->Container->ActivityData->MaxHeartRate);
    }
}
