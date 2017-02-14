<?php

namespace Runalyze\Tests\Metrics\Velocity\Unit;

use Runalyze\Metrics\Velocity\Unit\SecondsPer100m;

class SecondsPer100mTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new SecondsPer100m();

        $this->assertEquals(30, $unit->fromBaseUnit(300));
        $this->assertEquals(300, $unit->toBaseUnit(30));
    }
}
