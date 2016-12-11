<?php

namespace Runalyze\Tests\Metrics\Cadence\Unit;

use Runalyze\Metrics\Cadence\Unit\StepsPerMinute;

class StepsPerMinuteTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new StepsPerMinute();

        $this->assertEquals(180, $unit->fromBaseUnit(90));
        $this->assertEquals(85, $unit->toBaseUnit(170));
    }
}
