<?php

namespace Runalyze\Tests\Metrics\Temperature\Unit;

use Runalyze\Metrics\Temperature\Unit\Fahrenheit;

class FahrenheitTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new Fahrenheit();

        $this->assertEquals(32, $unit->fromBaseUnit(0));
        $this->assertEquals(0, $unit->toBaseUnit(32));

        $this->assertEquals(50, $unit->fromBaseUnit(10));
        $this->assertEquals(10, $unit->toBaseUnit(50));

        $this->assertEquals(68, $unit->fromBaseUnit(20));
        $this->assertEquals(20, $unit->toBaseUnit(68));

        $this->assertEquals(86, $unit->fromBaseUnit(30));
        $this->assertEquals(30, $unit->toBaseUnit(86));

        $this->assertEquals(14, $unit->fromBaseUnit(-10));
        $this->assertEquals(-10, $unit->toBaseUnit(14));
    }
}
