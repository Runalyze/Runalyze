<?php

namespace Runalyze\Data\Weather;

use Runalyze\Activity\Pace;
use Runalyze\Activity\Temperature as ActivityTemperature;
use Runalyze\Parameter\Application\TemperatureUnit;

class WindChillFactorTest extends \PHPUnit_Framework_TestCase
{
    /** @expectedException \InvalidArgumentException */
    public function testUnknownInputData()
    {
        $WindChill = new WindChillFactor(
            new WindSpeed(20),
            new ActivityTemperature()
        );
    }

    public function testSimpleExample()
    {
        $WindChill = new WindChillFactor(
            new WindSpeed(20),
            new ActivityTemperature(10),
            new Pace(5000, 5)
        );

        $this->assertEquals(7.1, $WindChill->value(), '', 0.1);
    }

    public function testThatObjectIsCloned()
    {
        $Temperature = new ActivityTemperature(37.16, new TemperatureUnit(TemperatureUnit::FAHRENHEIT));
        $WindChill = new WindChillFactor(
            new WindSpeed(0),
            $Temperature
        );

        $this->assertEquals($Temperature->valueInPreferredUnit(), $WindChill->adjustedTemperature()->valueInPreferredUnit());
        $this->assertEquals($Temperature->string(true, 2), $WindChill->string(true, 2));
    }
}
