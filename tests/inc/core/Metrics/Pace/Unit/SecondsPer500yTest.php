<?php

namespace Runalyze\Tests\Metrics\Pace\Unit;

use Runalyze\Metrics\Pace\Unit\SecondsPer500y;

class SecondsPer500yTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new SecondsPer500y();

        $this->assertEquals(137, $unit->fromBaseUnit(300), '', 0.5);
        $this->assertEquals(300, $unit->toBaseUnit(137), '', 0.5);
    }
}
