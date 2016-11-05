<?php

namespace Runalyze\Tests\Metrics\Pace\Unit;

use Runalyze\Metrics\Pace\Unit\MilesPerHour;

class MilesPerHourTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new MilesPerHour();

        $this->assertEquals(7.46, $unit->fromBaseUnit(300), '', 0.01);
        $this->assertEquals(300, $unit->toBaseUnit(7.46), '', 0.5);

        $this->assertEquals(6.21, $unit->fromBaseUnit(360), '', 0.01);
        $this->assertEquals(360, $unit->toBaseUnit(6.21), '', 0.5);
    }
}
