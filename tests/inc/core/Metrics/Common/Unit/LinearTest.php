<?php

namespace Runalyze\Tests\Metrics\Common\Unit;

use Runalyze\Metrics\Common\Unit\Linear;

class LinearTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleFunction()
    {
        $unit = new Linear(
            function ($value) { return 2.0 * $value + 5.0; },
            function ($value) { return ($value - 5.0) / 2.0; },
            'foo', 1
        );

        $this->assertEquals(7.0, $unit->fromBaseUnit(1.0));
        $this->assertEquals(1.0, $unit->toBaseUnit(7.0));
        $this->assertEquals('foo', $unit->getAppendix());
    }
}
