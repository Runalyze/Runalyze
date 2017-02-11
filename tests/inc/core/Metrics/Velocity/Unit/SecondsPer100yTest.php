<?php

namespace Runalyze\Tests\Metrics\Velocity\Unit;

use Runalyze\Metrics\Velocity\Unit\SecondsPer100y;

class SecondsPer100yTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new SecondsPer100y();

        $this->assertEquals(27.4, $unit->fromBaseUnit(300), '', 0.5);
        $this->assertEquals(300, $unit->toBaseUnit(27.4), '', 0.5);
    }
}
