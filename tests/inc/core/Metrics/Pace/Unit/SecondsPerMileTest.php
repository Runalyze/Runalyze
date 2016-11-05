<?php

namespace Runalyze\Tests\Metrics\Pace\Unit;

use Runalyze\Metrics\Pace\Unit\SecondsPerMile;

class SecondsPerMileTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new SecondsPerMile();

        $this->assertEquals(482.8, $unit->fromBaseUnit(300), '', 0.1);
        $this->assertEquals(300, $unit->toBaseUnit(482.8), '', 0.1);
    }
}
