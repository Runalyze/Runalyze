<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class UniformTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeValuesForWidth2()
    {
        $Kernel = new Uniform(2);

        $this->assertEquals([
            0, 0, 1, 1, 1, 1, 1, 0, 0, 0
        ], $Kernel->valuesAt([
            -2, -1.1, -1, -0.5, 0.0, 0.9, 1.0, 1.01, 1.5, 42
        ]));
    }
}
