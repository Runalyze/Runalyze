<?php

namespace Runalyze\Tests\Metrics\Distance\Unit;

use Runalyze\Metrics\Distance\Unit\Miles;

class MilesTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new Miles();

        $this->assertEquals(1.0, $unit->fromBaseUnit(1.609), '', 0.001);
        $this->assertEquals(1.609, $unit->toBaseUnit(1.0), '', 0.001);

        $this->assertEquals(10.0, $unit->fromBaseUnit(16.1), '', 0.01);
        $this->assertEquals(16.1, $unit->toBaseUnit(10), '', 0.01);

        $this->assertEquals(26.2, $unit->fromBaseUnit(42.2), '', 0.1);
        $this->assertEquals(42.2, $unit->toBaseUnit(26.2), '', 0.1);
    }
}
