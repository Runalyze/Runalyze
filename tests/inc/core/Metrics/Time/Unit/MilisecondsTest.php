<?php

namespace Runalyze\Tests\Metrics\Time\Unit;

use Runalyze\Metrics\Time\Unit\Miliseconds;

class MilisecondsTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new Miliseconds();

        $this->assertEquals(234.0, $unit->fromBaseUnit(0.234));
        $this->assertEquals(0.75, $unit->toBaseUnit(750));
    }
}
