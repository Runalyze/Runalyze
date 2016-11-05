<?php

namespace Runalyze\Tests\Metrics\Distance\Unit;

use Runalyze\Metrics\Distance\Unit\DistanceEnum;

class DistanceEnumTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllUnitsCanBeConstructed()
    {
        foreach (DistanceEnum::getEnum() as $unit) {
            DistanceEnum::get($unit);
        }
    }
}
