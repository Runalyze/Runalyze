<?php

namespace Runalyze\Tests\Metrics\Velocity\Unit;

use Runalyze\Metrics\Velocity\Unit\SecondsPerKilometer;

class SecondsPerKilometerTest extends \PHPUnit_Framework_TestCase
{
    public function testThatNothingChanges()
    {
        $unit = new SecondsPerKilometer();

        $this->assertEquals(300, $unit->fromBaseUnit(300));
        $this->assertEquals(300, $unit->toBaseUnit(300));
    }
}
