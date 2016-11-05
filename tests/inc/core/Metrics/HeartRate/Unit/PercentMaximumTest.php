<?php

namespace Runalyze\Tests\Metrics\HeartRate\Unit;

use Runalyze\Metrics\HeartRate\Unit\PercentMaximum;

class PercentMaximumTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new PercentMaximum(200);

        $this->assertEquals(200, $unit->getMaximalHeartRate());

        $this->assertEquals(0.5, $unit->fromBaseUnit(100));
        $this->assertEquals(100, $unit->toBaseUnit(0.5));

        $this->assertEquals(0.75, $unit->fromBaseUnit(150));
        $this->assertEquals(150, $unit->toBaseUnit(0.75));
    }

    public function testWithOtherMaximalHeartRate()
    {
        $unit = new PercentMaximum(180);

        $this->assertEquals(180, $unit->getMaximalHeartRate());

        $this->assertEquals(0.67, $unit->fromBaseUnit(120), '', 0.01);
        $this->assertEquals(120.6, $unit->toBaseUnit(0.67), '', 0.01);

        $this->assertEquals(0.75, $unit->fromBaseUnit(135));
        $this->assertEquals(135, $unit->toBaseUnit(0.75));
    }
}
