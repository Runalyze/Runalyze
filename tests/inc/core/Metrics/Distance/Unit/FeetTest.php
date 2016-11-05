<?php

namespace Runalyze\Tests\Metrics\Distance\Unit;

use Runalyze\Metrics\Distance\Unit\Feet;

class FeetTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new Feet();

        $this->assertEquals(3.28, $unit->fromBaseUnit(0.001), '', 0.01);
        $this->assertEquals(0.001, $unit->toBaseUnit(3.28), '', 0.01);
    }
}
