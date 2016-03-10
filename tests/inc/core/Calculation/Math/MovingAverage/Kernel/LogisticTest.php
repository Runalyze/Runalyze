<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class LogisticTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeValuesForDefaultWidth()
    {
        $Kernel = new Logistic(10.0);

        $this->assertEquals(0.0009, $Kernel->at(-7.0), '', 0.0001);
        $this->assertEquals(0.0066, $Kernel->at(-5.0), '', 0.0001);
        $this->assertEquals(0.0701, $Kernel->at(-2.5), '', 0.0001);
        $this->assertEquals(0.2500, $Kernel->at(0.0), '', 0.0001);
        $this->assertEquals(0.0701, $Kernel->at(2.5), '', 0.0001);
        $this->assertEquals(0.0066, $Kernel->at(5.0), '', 0.0001);
        $this->assertEquals(0.0009, $Kernel->at(7.0), '', 0.0001);
    }
}
