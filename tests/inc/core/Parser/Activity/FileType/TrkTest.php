<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\FileType\Trk;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class TrkTest extends AbstractActivityParserTestCase
{
    /** @var Trk */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Trk();
    }

    public function testMinimalExample()
    {
        $this->parseFile($this->Parser, 'trk/minimal-example.trk');

        $this->assertEquals('06-04-2015 15:37:38', LocalTime::date('d-m-Y H:i:s', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(
            [0, 1, 2, 3, 4, 5, 6],
            $this->Container->ContinuousData->Time
        );
        $this->assertEquals(
            [108, 108, 107, 107, 107, 107, 107],
            $this->Container->ContinuousData->HeartRate
        );
        $this->assertEquals(
            [15,  15,  15,  16,  16,  16,  16],
            $this->Container->ContinuousData->Temperature
        );
        $this->assertEquals(
            [189, 189, 189, 189, 189, 189, 189],
            $this->Container->ContinuousData->Altitude
        );

        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
    }

    public function testFileWithPause()
    {
        $this->parseFile($this->Parser, 'trk/with-pause.trk');

        $this->assertEquals('12-04-2015 11:23:00', LocalTime::date('d-m-Y H:i:s', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(range(0, 20), $this->Container->ContinuousData->Time);
        $this->assertEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
    }
}
