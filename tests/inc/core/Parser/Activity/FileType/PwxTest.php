<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\FileType\Pwx;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class PwxTest extends AbstractActivityParserTestCase
{
    /** @var Pwx */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Pwx();
    }

    public function testFileWithoutDistance()
    {
        $this->parseFile($this->Parser, 'pwx/without-dist.pwx');

        $this->assertEquals('2009-02-10 06:15', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(null, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(1646, $this->Container->ActivityData->Duration, '', 30);
        $this->assertEquals(4.891, $this->Container->ActivityData->Distance, '', 0.1);

        $this->assertEquals('Stuart', $this->Container->Metadata->getDescription());
        $this->assertEquals("Apple, iPhone (SERIAL_NUMBER)", $this->Container->Metadata->getCreatorDetails());

        $this->assertTrue($this->Container->Rounds->isEmpty());
    }

    /**
     * Test: standard file
     * Filename: "with-dist.pwx"
     */
    public function test_withDist() {
        $this->parseFile($this->Parser, 'pwx/with-dist.pwx');

        $this->assertEquals('2008-11-16 11:40', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(null, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(6978, $this->Container->ActivityData->Duration, '', 30);
        $this->assertEquals(16.049, $this->Container->ActivityData->Distance, '', 0.1);

        $this->assertEquals('Blue Sky trail with Dan and Ian', $this->Container->Metadata->getDescription());
        $this->assertEquals("Garmin, Edge 205/305 (EDGE305 Software Version 3.20)", $this->Container->Metadata->getCreatorDetails());

        $this->assertEquals(4, $this->Container->Rounds->count());
    }

    /**
     * Test: standard file
     * Filename: "with-dist-and-hr.pwx"
     */
    public function test_withDistAndHr() {
        $this->parseFile($this->Parser, 'pwx/with-dist-and-hr.pwx');

        $this->assertEquals(13539, $this->Container->ActivityData->Duration, '', 30);
        $this->assertEquals(89.535, $this->Container->ActivityData->Distance, '', 0.1);
        $this->assertEquals(148, $this->Container->ActivityData->AvgHeartRate, '', 2);
        $this->assertEquals(174, $this->Container->ActivityData->MaxHeartRate, '', 2);
    }

    public function testFileWithPower()
    {
        $this->parseFile($this->Parser, 'pwx/with-power.pwx');

        $this->assertNotEmpty($this->Container->ContinuousData->Power);
        $this->assertGreaterThan(0, $this->Container->ActivityData->AvgPower);

        $this->assertEquals(18, $this->Container->Rounds->count());
    }

    public function testIntervals()
    {
        $this->parseFile($this->Parser, 'pwx/intervals.pwx');

        $this->assertEquals('2015-08-05', LocalTime::date('Y-m-d', $this->Container->Metadata->getTimestamp()));

        $this->assertEquals(4813 - 289, $this->Container->ActivityData->Duration, '', 30);
        $this->assertEquals(15.00, $this->Container->ActivityData->Distance, '', 0.1);

        $this->assertEquals(9, $this->Container->Rounds->count());

        $this->checkExpectedPauseData([
            [635, 3, 155, 154],
            [640, 286, 154, 0]
        ]);
    }
}
