<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class GaussianTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeValuesForDefaultWidth()
    {
        $Kernel = new Gaussian(6.0);

        $this->assertEquals(0.011, $Kernel->at(-3.0), '', 0.001);
        $this->assertEquals(0.135, $Kernel->at(-2.0), '', 0.001);
        $this->assertEquals(0.607, $Kernel->at(-1.0), '', 0.001);
        $this->assertEquals(1.000, $Kernel->at(0.0), '', 0.001);
        $this->assertEquals(0.607, $Kernel->at(1.0), '', 0.001);
        $this->assertEquals(0.135, $Kernel->at(2.0), '', 0.001);
        $this->assertEquals(0.011, $Kernel->at(3.0), '', 0.001);
    }
}
