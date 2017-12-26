<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\FileType\Logbook;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class LogbookTest extends AbstractActivityParserTestCase
{
    /** @var Logbook */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Logbook();
    }

    public function testMinifiedLogbook()
    {
        $this->parseFile($this->Parser, 'sporttracks/test.logbook');

        $this->assertTrue(is_array($this->Container));
        $this->assertEquals(5, count($this->Container));

        $this->assertEquals('2008-09-06 18:01', LocalTime::date('Y-m-d H:i', $this->Container[0]->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container[0]->Metadata->getTimezoneOffset());
        $this->assertEquals(9382, $this->Container[0]->ActivityData->Duration);
        $this->assertEquals(26.743, $this->Container[0]->ActivityData->Distance);
        $this->assertEquals(943, $this->Container[0]->ActivityData->EnergyConsumption);
        $this->assertEquals("Buxtehuder Abendlauf", $this->Container[0]->Metadata->getDescription());
        $this->assertEquals("Buxtehude", $this->Container[0]->Metadata->getRouteDescription());
        $this->assertEquals("20°C\r\n1-2 Bft", $this->Container[0]->Metadata->getNotes());
        $this->assertEquals("Clouds", $this->Container[0]->WeatherData->Condition);

        $this->assertEquals('2009-03-28 15:03', LocalTime::date('Y-m-d H:i', $this->Container[1]->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container[1]->Metadata->getTimezoneOffset());
        $this->assertEquals(10837, $this->Container[1]->ActivityData->Duration);
        $this->assertEquals(25.864, $this->Container[1]->ActivityData->Distance);
        $this->assertEquals(365, $this->Container[1]->ActivityData->ElevationAscent);
        $this->assertEquals(156, $this->Container[1]->ActivityData->AvgHeartRate);
        $this->assertEquals(167, $this->Container[1]->ActivityData->MaxHeartRate);
        $this->assertEquals("mit Michael", $this->Container[1]->Metadata->getDescription());
        $this->assertEquals("Horneburg-Helmste-Harsefeld-Bliedersdorf", $this->Container[1]->Metadata->getRouteDescription());
        $this->assertEquals("Erster Lauf mit der Forerunner 305  ;o)", $this->Container[1]->Metadata->getNotes());
        $this->assertEquals("LightRain", $this->Container[1]->WeatherData->Condition);
        $this->assertTrue($this->Container[1]->Rounds->isEmpty());

        $this->assertEquals('2009-03-31 20:22', LocalTime::date('Y-m-d H:i', $this->Container[2]->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container[2]->Metadata->getTimezoneOffset());
        $this->assertEquals(2310, $this->Container[2]->ActivityData->Duration);
        $this->assertEquals(6.904, $this->Container[2]->ActivityData->Distance);

        $this->checkExpectedRoundDataFor($this->Container[2], [
            [337, 1.000],
            [338, 1.000],
            [343, 1.000],
            [338, 1.000],
            [337, 1.000],
            [319, 1.000],
            [296, 0.904]
        ]);

        $this->assertEquals("Rennrad", $this->Container[4]->Metadata->getSportName());
        $this->assertEquals(19.0, $this->Container[4]->WeatherData->Temperature, '', 0.5);
    }
}
