<?php

namespace Runalyze\Tests\Metrics\Temperature\Unit;

use Runalyze\Metrics\Temperature\Unit\TemperatureEnum;

class TemperatureEnumTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllUnitsCanBeConstructed()
    {
        foreach (TemperatureEnum::getEnum() as $unit) {
            TemperatureEnum::get($unit);
        }
    }
}
