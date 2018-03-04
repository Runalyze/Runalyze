<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\FileType\XmlSuunto;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class XmlSuuntoTest extends AbstractActivityParserTestCase
{
    /** @var XmlSuunto */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new XmlSuunto();
    }

    public function testIncorrectXmlFile()
    {
        $this->Parser->setFileContent('<?xml version="1.0" encoding="utf-8"?><any><xml><file></file></xml></any>');

        $this->setExpectedException(UnsupportedFileException::class);

        $this->Parser->parse();
    }

    public function testReducedAmbitFile()
    {
        $this->parseFile($this->Parser, 'xml/Suunto-Ambit-reduced.xml');

        $this->assertEquals('2013-04-28 15:27', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));

        $this->assertEquals(0.264, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(107, $this->Container->ActivityData->Duration);
        $this->assertEquals(151, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(461, $this->Container->ActivityData->EnergyConsumption);
        $this->assertEquals(131, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(143, $this->Container->ActivityData->MaxHeartRate);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Temperature);

        $this->assertEquals(0.264, end($this->Container->ContinuousData->Distance), '', 0.001);
    }

    public function testReducedAmbitFileWithLaps()
    {
        $this->parseFile($this->Parser, 'xml/Suunto-Ambit-with-laps-reduced.xml');

        $this->assertEquals('2014-04-26 16:17', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));

        $this->assertEquals(5.013, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(1551, $this->Container->ActivityData->Duration);
        $this->assertEquals(361, $this->Container->ActivityData->EnergyConsumption);
        $this->assertEquals(111, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(123, $this->Container->ActivityData->MaxHeartRate);
        $this->assertEquals(86, $this->Container->ActivityData->AvgCadence, '', 0.5);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->Temperature);

        $this->assertEquals(0.085, end($this->Container->ContinuousData->Distance), '', 0.001);

        $this->checkExpectedRoundData([
            [333, 1.002],
            [316, 1.000]
        ]);
    }
}
