<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class TriangularTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeValuesForWidth2()
    {
        $Kernel = new Triangular(2);

        $this->assertEquals([
            0.0, 0.0, 0.1, 0.5, 1.0, 0.25, 0.0
        ], $Kernel->valuesAt([
            -1.5, -1.0, -0.9, -0.5, 0.0, 0.75, 1.1
        ]));
    }

    public function testSomeValuesForWidth10()
    {
        $Kernel = new Triangular(10);

        $this->assertEquals([
            0.0, 0.2, 0.4, 0.6, 0.8, 1.0, 0.8, 0.6, 0.4, 0.2, 0.0
        ], $Kernel->valuesAt([
            -5.0, -4.0, -3.0, -2.0, -1.0, 0.0, 1.0, 2.0, 3.0, 4.0, 5.0
        ]));
    }
}
