<?php

namespace Runalyze\Tests\Metrics\Pace\Unit;

use Runalyze\Metrics\Pace\Unit\SecondsPer500m;

class SecondsPer500mTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new SecondsPer500m();

        $this->assertEquals(150, $unit->fromBaseUnit(300));
        $this->assertEquals(300, $unit->toBaseUnit(150));
    }
}
