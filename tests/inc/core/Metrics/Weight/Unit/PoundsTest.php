<?php

namespace Runalyze\Tests\Metrics\Weight\Unit;

use Runalyze\Metrics\Weight\Unit\Pounds;

class PoundsTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new Pounds();

        $this->assertEquals(165.3, $unit->fromBaseUnit(75), '', 0.1);
        $this->assertEquals(75, $unit->toBaseUnit(165.3), '', 0.1);

        $this->assertEquals(10, $unit->fromBaseUnit(4.54), '', 0.1);
        $this->assertEquals(4.54, $unit->toBaseUnit(10), '', 0.1);

        $this->assertEquals(22.04, $unit->fromBaseUnit(10), '', 0.1);
        $this->assertEquals(10, $unit->toBaseUnit(22.04), '', 0.1);
    }
}
