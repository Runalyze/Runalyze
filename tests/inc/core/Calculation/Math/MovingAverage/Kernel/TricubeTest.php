<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class TricubeTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeValuesForWidth2()
    {
        $Kernel = new Tricube(2);

        $this->assertEquals([
            0.0,
            0.669921875,
            1.0,
            0.193225860595703125
        ], $Kernel->valuesAt([
            -1.0,
            -0.5,
            0.0,
            0.75
        ]));
    }

    public function testSomeValuesForWidth10()
    {
        $Kernel = new Tricube(10);

        $this->assertEquals([
            0.0,
            0.669921875,
            1.0,
            0.481890304
        ], $Kernel->valuesAt([
            -5.0,
            -2.5,
            0.0,
            3.0
        ]));
    }
}
