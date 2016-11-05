<?php

namespace Runalyze\Tests\Metrics\Distance\Unit;

use Runalyze\Metrics\Distance\Unit\Meter;

class MeterTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new Meter();

        $this->assertEquals(1000, $unit->fromBaseUnit(1.0));
        $this->assertEquals(1.0, $unit->toBaseUnit(1000));

        $this->assertEquals(3141, $unit->fromBaseUnit(3.141));
        $this->assertEquals(3.141, $unit->toBaseUnit(3141));
    }
}
