<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class CosineTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeValuesForDefaultWidth()
    {
        $Kernel = new Cosine(2.0);

        $this->assertEquals(0.000, $Kernel->at(-1.0), '', 0.001);
        $this->assertEquals(0.707, $Kernel->at(-0.5), '', 0.001);
        $this->assertEquals(1.000, $Kernel->at(0.0), '', 0.001);
        $this->assertEquals(0.707, $Kernel->at(0.5), '', 0.001);
        $this->assertEquals(0.000, $Kernel->at(1.0), '', 0.001);
    }
}
