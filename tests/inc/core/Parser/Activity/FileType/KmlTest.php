<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Converter\KmzConverter;
use Runalyze\Parser\Activity\FileType\Kml;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class KmlTest extends AbstractActivityParserTestCase
{
    /** @var Kml */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Kml();
    }

    public function testIncorrectXmlFile()
    {
        $this->Parser->setFileContent('<any><xml><file></file></xml></any>');

        $this->setExpectedException(UnsupportedFileException::class);

        $this->Parser->parse();
    }

    public function testStandardFileWithRouteOnly()
    {
        $this->parseFile($this->Parser, 'kml/Route-only.kml');

        $this->assertEquals(0.4, $this->Container->ActivityData->Distance, '', 0.05);

        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);

        $this->assertEmpty($this->Container->ContinuousData->Time);
    }

    public function testStandardFileWithRouteOnlyWithZeros()
    {
        $this->parseFile($this->Parser, 'kml/Route-only-with-zeros.kml');

        $this->assertEquals(0.4, $this->Container->ActivityData->Distance, '', 0.05);

        $this->assertEquals(11, count($this->Container->ContinuousData->Distance));
    }

    public function testStandardFileMultiLineWithoutAltitude()
    {
        $this->parseFile($this->Parser, 'kml/multi-line-without-altitude.kml');

        $this->assertEquals(2.25, $this->Container->ActivityData->Distance, '', 0.05);

        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);

        $this->assertEmpty($this->Container->ContinuousData->Time);
        $this->assertEmpty($this->Container->ContinuousData->Altitude);
    }

    public function testTomTomFile()
    {
        $this->parseFile($this->Parser, 'kml/TomTom.kml');

        // assume that '...Z' time strings are converted to Europe/Berlin (see bootstrap.php)
        $this->assertEquals('2013-09-08 10:34', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(3637, $this->Container->ActivityData->Duration);
        $this->assertEquals(3788, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(12.816, $this->Container->ActivityData->Distance, '', 0.1);
        $this->assertEquals(808, $this->Container->ActivityData->EnergyConsumption, '', 10);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
    }

    public function testTomTomFileWithoutDistance()
    {
        $this->parseFile($this->Parser, 'kml/TomTom-without-distance-extension.kml');

        // assume that '...Z' time strings are converted to Europe/Berlin (see bootstrap.php)
        $this->assertEquals('2015-03-15 07:29', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(5 * 3600 + 51 * 60 + 51, $this->Container->ActivityData->Duration);
        $this->assertEquals(12.816, $this->Container->ActivityData->Distance, '', 0.1);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
    }

    public function testSpartanUltraFileWithTemperature()
    {
        $this->parseFile($this->Parser, 'kml/Suunto-Spartan-Ultra.kml');

        $this->assertEquals(0.098, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->Temperature);
    }

    public function testZippedFile()
    {
        $this->convertAndParseFile(new KmzConverter(), $this->Parser, 'kmz/Baechenstock.kmz', [
            'kmz/Baechenstock.kmz.doc.kml'
        ]);

        $this->assertEquals(12.896, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
    }
}
