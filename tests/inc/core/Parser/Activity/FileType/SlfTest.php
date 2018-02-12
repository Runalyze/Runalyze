<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\FileType\Slf;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class SlfTest extends AbstractActivityParserTestCase
{
    /** @var Slf */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Slf();
    }

    public function testStandardFileWithoutDistance()
    {
        $this->parseFile($this->Parser, 'slf/Standard.slf');

        $this->assertEquals('2012-04-29 12:58', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(1257, $this->Container->ActivityData->Duration);
        $this->assertEquals(1357, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(5.282, $this->Container->ActivityData->Distance, '', 0.1);
        $this->assertEquals(163, $this->Container->ActivityData->AvgHeartRate, '', 1);
        $this->assertEquals(174, $this->Container->ActivityData->MaxHeartRate);
        $this->assertEquals(306, $this->Container->ActivityData->EnergyConsumption, '', 1);

        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
    }

    public function testAnotherFile()
    {
        $this->parseFile($this->Parser, 'slf/2012_10_14__13_19_.slf');

        $this->assertEquals('2012-10-14 13:19', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(1803, $this->Container->ActivityData->Duration, '', 30);
        $this->assertEquals(4.109, $this->Container->ActivityData->Distance, '', 0.1);
        $this->assertEquals(120, $this->Container->ActivityData->AvgHeartRate, '', 1);
        $this->assertEquals(135, $this->Container->ActivityData->MaxHeartRate);
        $this->assertEquals(243, $this->Container->ActivityData->EnergyConsumption, '', 1);
    }

    public function testVersion4File()
    {
        $this->parseFile($this->Parser, 'slf/DatacenterVersion4-HM.slf');

        $this->assertEquals('2015-03-29 11:10', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(5559, $this->Container->ActivityData->Duration);
        $this->assertEquals(20.88, $this->Container->ActivityData->Distance, '', 0.1);
        $this->assertEquals(163, $this->Container->ActivityData->AvgHeartRate, '', 1);
        $this->assertEquals(169, $this->Container->ActivityData->MaxHeartRate);
        $this->assertEquals(1068, $this->Container->ActivityData->EnergyConsumption, '', 1);

        $this->assertEquals(14, $this->Container->Rounds->count());
    }

    public function testVersion4WithoutEntriesFile()
    {
        $this->parseFile($this->Parser, 'slf/slf4-without-entries-.slf');

        $this->assertEquals('2015-02-03 10:01', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(2766, $this->Container->ActivityData->Duration);
        $this->assertEquals(7.42, $this->Container->ActivityData->Distance, '', 0.1);
        $this->assertEquals(138, $this->Container->ActivityData->AvgHeartRate, '', 1);
        $this->assertEquals(162, $this->Container->ActivityData->MaxHeartRate);
        $this->assertEquals(404, $this->Container->ActivityData->EnergyConsumption, '', 1);

        $this->assertTrue($this->Container->Rounds->isEmpty());
    }
}
