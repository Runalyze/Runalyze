<?php

namespace Runalyze\Tests\Metrics\Weight\Unit;

use Runalyze\Metrics\Weight\Unit\Stones;

class StonesTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new Stones();

        $this->assertEquals(11.8, $unit->fromBaseUnit(75), '', 0.1);
        $this->assertEquals(75, $unit->toBaseUnit(11.8), '', 0.1);

        $this->assertEquals(10, $unit->fromBaseUnit(63.5), '', 0.1);
        $this->assertEquals(63.5, $unit->toBaseUnit(10), '', 0.1);

        $this->assertEquals(1.57, $unit->fromBaseUnit(10), '', 0.1);
        $this->assertEquals(10, $unit->toBaseUnit(1.57), '', 0.1);
    }
}
