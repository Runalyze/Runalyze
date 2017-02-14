<?php

namespace Runalyze\Tests\Metrics\Velocity\Unit;

use Runalyze\Metrics\Velocity\Unit\MeterPerSecond;

class MeterPerSecondTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new MeterPerSecond();

        $this->assertEquals(3.333, $unit->fromBaseUnit(300), '', 0.001);
        $this->assertEquals(300, $unit->toBaseUnit(3.333), '', 0.5);

        $this->assertEquals(2.777, $unit->fromBaseUnit(360), '', 0.001);
        $this->assertEquals(360, $unit->toBaseUnit(2.777), '', 0.5);
    }
}
