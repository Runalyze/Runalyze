<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\FileType\XmlPolar;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class XmlPolarTest extends AbstractActivityParserTestCase
{
    /** @var XmlPolar */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new XmlPolar();
    }

    public function testStandardFile()
    {
        $this->parseFile($this->Parser, 'xml/Polar.xml');

        $this->assertEquals('2013-03-24 11:33', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(6.6, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(48 * 60 + 49, $this->Container->ActivityData->Duration);
        $this->assertEquals(725, $this->Container->ActivityData->EnergyConsumption);
        $this->assertEquals(156, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(179, $this->Container->ActivityData->MaxHeartRate);

        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
    }

    public function testFileWithArrays()
    {
        $this->parseFile($this->Parser, 'xml/Polar-with-arrays.xml');

        $this->assertEquals('2014-09-07 09:58', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(20.05, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(7740, $this->Container->ActivityData->Duration, '', 0.5);
        $this->assertEquals(2015, $this->Container->ActivityData->EnergyConsumption);
        $this->assertEquals(157, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(173, $this->Container->ActivityData->MaxHeartRate);

        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);

        $this->assertEquals(20.049, end($this->Container->ContinuousData->Distance), '', 0.001);

        $this->checkExpectedRoundData(array_fill(0, 20, [300, 1.000]), 300, 0.001);
    }

    public function testFileWithArraysAndAdditionalCommaAtTheEnd()
    {
        $this->parseFile($this->Parser, 'xml/Polar-additional-comma.xml');

        $this->assertSameSize($this->Container->ContinuousData->HeartRate, $this->Container->ContinuousData->Altitude);
        $this->assertSameSize($this->Container->ContinuousData->HeartRate, $this->Container->ContinuousData->Cadence);
    }

    public function testFileWithMultipleActivities()
    {
        $this->parseFile($this->Parser, 'xml/Multiple-Polar.xml');

        $this->assertEquals(6, count($this->Container));

        $this->assertEquals(
            LocalTime::mktime(7, 9, 34, 5, 11, 2011),
            $this->Container[0]->Metadata->getTimestamp()
        );
        $this->assertEquals(17.1, $this->Container[0]->ActivityData->Distance);

        $this->assertEquals(LocalTime::mktime(17, 31, 19, 5, 11, 2011),
            $this->Container[1]->Metadata->getTimestamp()
        );
        $this->assertEquals(16.7, $this->Container[1]->ActivityData->Distance);

        $this->assertEquals(LocalTime::mktime(7, 14, 49, 5, 12, 2011),
            $this->Container[2]->Metadata->getTimestamp()
        );
        $this->assertEquals(17.0, $this->Container[2]->ActivityData->Distance);

        $this->assertEquals(LocalTime::mktime(17, 35, 32, 5, 12, 2011),
            $this->Container[3]->Metadata->getTimestamp()
        );
        $this->assertEquals(16.5, $this->Container[3]->ActivityData->Distance);

        $this->assertEquals(LocalTime::mktime(7, 12, 15, 5, 13, 2011),
            $this->Container[4]->Metadata->getTimestamp()
        );

        $this->assertEquals(LocalTime::mktime(17, 5, 28, 5, 13, 2011),
            $this->Container[5]->Metadata->getTimestamp()
        );
    }

    public function testFileWithLapsWithoutDistance()
    {
        $this->parseFile($this->Parser, 'xml/Polar-lap-without-distance.xml');

        $this->assertCount(2, $this->Container);

        // First activity: cycling
        $this->assertEquals(3011, $this->Container[0]->ActivityData->Duration, '', 0.5);
        $this->assertEquals(149, $this->Container[0]->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(188, $this->Container[0]->ActivityData->MaxHeartRate);
        $this->assertEquals(643, $this->Container[0]->ActivityData->EnergyConsumption);
        $this->assertNull($this->Container[0]->ActivityData->Distance);

        $this->assertNotEmpty($this->Container[0]->ContinuousData->HeartRate);
        $this->assertEmpty($this->Container[0]->ContinuousData->Distance);

        // First activity: running
        $this->assertEquals(1373, $this->Container[1]->ActivityData->Duration, '', 0.5);
        $this->assertEquals(159, $this->Container[1]->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(180, $this->Container[1]->ActivityData->MaxHeartRate);
        $this->assertEquals(326, $this->Container[1]->ActivityData->EnergyConsumption);
        $this->assertNull($this->Container[1]->ActivityData->Distance);

        $this->assertNotEmpty($this->Container[1]->ContinuousData->HeartRate);
        $this->assertEmpty($this->Container[1]->ContinuousData->Distance);
    }
}
