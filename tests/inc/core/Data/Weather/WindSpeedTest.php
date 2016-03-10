<?php

namespace Runalyze\Data\Weather;

use Runalyze\Parameter\Application\DistanceUnitSystem;

class WindSpeedTest extends \PHPUnit_Framework_TestCase
{
    public function testIsUnknown()
    {
        $WindSpeed = new WindSpeed();

        $this->assertTrue($WindSpeed->isUnknown());
        $this->assertEquals(null, $WindSpeed->value());
    }

    public function testMetric()
    {
        $WindSpeed = new WindSpeed(30);
        $this->assertEquals(30, $WindSpeed->value());
        $this->assertEquals('km/h', $WindSpeed->unit());
        $this->assertFalse($WindSpeed->isUnknown());
    }

    public function testImperial()
    {
        $WindSpeed = new WindSpeed();
        $WindSpeed->setMilesPerHour(30);
        $this->assertEquals(48.28, $WindSpeed->value(), '', 0.01);
        $this->assertEquals(30, $WindSpeed->inMilesPerHour(), '', 0.01);
        $this->assertFalse($WindSpeed->isUnknown());
    }

    public function testString()
    {
        $WindSpeed = new WindSpeed();

        $this->assertEquals('', $WindSpeed->string(false));
        $this->assertEquals('', $WindSpeed->string(true));
        $this->assertEquals('12.5', $WindSpeed->setKilometerPerHour(12.49)->string(false, 1));
        $this->assertEquals('12&nbsp;km/h', $WindSpeed->string(true));
    }

    public function testMetricUnit()
    {
        $WindSpeed = new WindSpeed(null, new DistanceUnitSystem(DistanceUnitSystem::METRIC));
        $WindSpeed->setInPreferredUnit(37);

        $this->assertEquals(37, $WindSpeed->value());
        $this->assertEquals(37, $WindSpeed->valueInPreferredUnit());
        $this->assertEquals('37&nbsp;km/h', $WindSpeed->string());
    }

    public function testImperialUnit()
    {
        $WindSpeed = new WindSpeed(null, new DistanceUnitSystem(DistanceUnitSystem::IMPERIAL));
        $WindSpeed->setInPreferredUnit(37);

        $this->assertNotEquals(37, $WindSpeed->value());
        $this->assertEquals(37, $WindSpeed->valueInPreferredUnit());
        $this->assertEquals('37&nbsp;mph', $WindSpeed->string());
    }
}
