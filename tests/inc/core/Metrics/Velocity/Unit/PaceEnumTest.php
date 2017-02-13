<?php

namespace Runalyze\Tests\Metrics\Velocity\Unit;

use Runalyze\Metrics\Velocity\Unit\PaceEnum;

class PaceEnumTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllUnitsCanBeConstructed()
    {
        foreach (PaceEnum::getEnum() as $unit) {
            PaceEnum::get($unit);
        }
    }
}
