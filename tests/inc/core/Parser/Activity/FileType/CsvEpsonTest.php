<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\FileType\CsvEpson;

/**
 * @group import
 */
class CsvEpsonTest extends AbstractActivityParserTestCase
{
    /** @var CsvEpson */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new CsvEpson();
    }

    /**
     * @group importerEpson
     * @see https://github.com/Runalyze/Runalyze/issues/1575
     */
    public function testEpsonFileWithDifferentArraySizes()
    {
        $this->parseFile($this->Parser, 'csv/Epson-different-array-sizes.csv');

        $sizeTimeData = count($this->Container->ContinuousData->Time);

        $this->assertEquals($sizeTimeData, count($this->Container->ContinuousData->Distance), 'Distance array has wrong size.');
        $this->assertEquals($sizeTimeData, count($this->Container->ContinuousData->Latitude), 'Latitude array has wrong size.');
        $this->assertEquals($sizeTimeData, count($this->Container->ContinuousData->Longitude), 'Longitude array has wrong size.');
        $this->assertEquals($sizeTimeData, count($this->Container->ContinuousData->Altitude), 'Altitude array has wrong size.');
        $this->assertEquals($sizeTimeData, count($this->Container->ContinuousData->HeartRate), 'Heartrate array has wrong size.');
        $this->assertEquals($sizeTimeData, count($this->Container->ContinuousData->Cadence), 'Cadence array has wrong size.');
    }
}
