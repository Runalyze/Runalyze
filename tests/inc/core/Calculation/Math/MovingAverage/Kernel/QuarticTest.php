<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class QuarticTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeValuesForWidth2()
    {
        $Kernel = new Quartic(2);

        $this->assertEquals([
            0.0,
            0.5625,
            1.0,
            0.19140625
        ], $Kernel->valuesAt([
            -1.0,
            -0.5,
            0.0,
            0.75
        ]));
    }

    public function testSomeValuesForWidth10()
    {
        $Kernel = new Quartic(10);

        $this->assertEquals([
            0.0,
            0.5625,
            1.0,
            0.4096
        ], $Kernel->valuesAt([
            -5.0,
            -2.5,
            0.0,
            3.0
        ]));
    }
}
