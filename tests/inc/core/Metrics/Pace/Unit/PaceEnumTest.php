<?php

namespace Runalyze\Tests\Metrics\Pace\Unit;

use Runalyze\Metrics\Pace\Unit\PaceEnum;

class PaceEnumTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllUnitsCanBeConstructed()
    {
        foreach (PaceEnum::getEnum() as $unit) {
            PaceEnum::get($unit);
        }
    }
}
