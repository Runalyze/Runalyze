<?php

namespace Runalyze\Tests\Metrics\Common\Unit;

use Runalyze\Metrics\Common\Unit\Factorial;

class FactorialTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeEasyValues()
    {
        $unit = new Factorial('foo', 10);

        $this->assertEquals(7.9, $unit->fromBaseUnit(0.79));
        $this->assertEquals(4.2, $unit->toBaseUnit(42));
        $this->assertEquals('foo', $unit->getAppendix());
    }

    public function testDecimals()
    {
        $this->assertEquals(2, (new Factorial('foo', 0.01))->getDecimals());
        $this->assertEquals(1, (new Factorial('foo', 0.2))->getDecimals());
        $this->assertEquals(0, (new Factorial('foo', 10))->getDecimals());
        $this->assertEquals(0, (new Factorial('foo', 10, 0))->getDecimals());
        $this->assertEquals(2, (new Factorial('foo', 10, 2))->getDecimals());
    }
}
