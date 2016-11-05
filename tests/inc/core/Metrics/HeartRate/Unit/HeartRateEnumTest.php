<?php

namespace Runalyze\Tests\Metrics\HeartRate\Unit;

use Runalyze\Metrics\HeartRate\Unit\HeartRateEnum;

class HeartRateEnumTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllUnitsCanBeConstructed()
    {
        foreach (HeartRateEnum::getEnum() as $unit) {
            HeartRateEnum::get($unit, 200, 60);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThatUnknownUnitCantBeConstructed()
    {
        HeartRateEnum::get(42, 200, 60);
    }
}
