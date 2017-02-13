<?php

namespace Runalyze\Tests\Metrics\Velocity\Unit;

use Runalyze\Metrics\Velocity\Unit\KilometerPerHour;

class KilometerPerHourTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new KilometerPerHour();

        $this->assertEquals(12, $unit->fromBaseUnit(300));
        $this->assertEquals(300, $unit->toBaseUnit(12));

        $this->assertEquals(10, $unit->fromBaseUnit(360));
        $this->assertEquals(360, $unit->toBaseUnit(10));
    }
}
