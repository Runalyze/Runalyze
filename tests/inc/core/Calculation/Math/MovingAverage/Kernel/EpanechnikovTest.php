<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

class EpanechnikovTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeValuesForWidth2()
    {
        $Kernel = new Epanechnikov(2);

        $this->assertEquals([
            0.0,
            0.75,
            1.0,
            0.4375
        ], $Kernel->valuesAt([
            -1.0,
            -0.5,
            0.0,
            0.75
        ]));
    }

    public function testSomeValuesForWidth10()
    {
        $Kernel = new Epanechnikov(10);

        $this->assertEquals([
            0.0,
            0.75,
            1.0,
            0.64
        ], $Kernel->valuesAt([
            -5.0,
            -2.5,
            0.0,
            3.0
        ]));
    }
}
