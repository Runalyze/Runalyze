<?php

namespace Runalyze\Tests\Metrics\Weight\Unit;

use Runalyze\Metrics\Weight\Unit\WeightEnum;

class WeightEnumTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllUnitsCanBeConstructed()
    {
        foreach (WeightEnum::getEnum() as $unit) {
            WeightEnum::get($unit);
        }
    }
}
