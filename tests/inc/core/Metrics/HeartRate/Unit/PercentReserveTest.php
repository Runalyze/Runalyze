<?php

namespace Runalyze\Tests\Metrics\HeartRate\Unit;

use Runalyze\Metrics\HeartRate\Unit\PercentReserve;

class PercentReserveTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new PercentReserve(200, 60);

        $this->assertEquals(200, $unit->getMaximalHeartRate());
        $this->assertEquals(60, $unit->getRestingHeartRate());

        $this->assertEquals(0.29, $unit->fromBaseUnit(100), '', 0.01);
        $this->assertEquals(100.6, $unit->toBaseUnit(0.29), '', 0.01);

        $this->assertEquals(0.71, $unit->fromBaseUnit(160), '', 0.01);
        $this->assertEquals(159.4, $unit->toBaseUnit(0.71), '', 0.01);
    }

    public function testWithOtherMaximalHeartRate()
    {
        $unit = new PercentReserve(175, 75);

        $this->assertEquals(175, $unit->getMaximalHeartRate());
        $this->assertEquals(75, $unit->getRestingHeartRate());

        $this->assertEquals(0.45, $unit->fromBaseUnit(120));
        $this->assertEquals(120, $unit->toBaseUnit(0.45));

        $this->assertEquals(0.75, $unit->fromBaseUnit(150));
        $this->assertEquals(150, $unit->toBaseUnit(0.75));
    }
}
