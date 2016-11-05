<?php

namespace Runalyze\Tests\Metrics\Energy\Unit;

use Runalyze\Metrics\Energy\Unit\EnergyEnum;

class EnergyEnumTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllUnitsCanBeConstructed()
    {
        foreach (EnergyEnum::getEnum() as $unit) {
            EnergyEnum::get($unit);
        }
    }
}
