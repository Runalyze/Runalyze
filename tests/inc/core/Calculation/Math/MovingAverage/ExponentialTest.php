<?php

namespace Runalyze\Calculation\Math\MovingAverage;

class ExponentialTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleExampleWithAlpha50percent()
    {
        $Object = new Exponential([10, 20, 30, 20, 10]);
        $Object->setAlpha(0.5);
        $Object->calculate();

        $this->assertEquals([
            10,
            15,
            22.5,
            21.25,
            15.625
        ], $Object->movingAverage());
    }

    public function testSimpleExampleWithAlpha75percent()
    {
        $Object = new Exponential([10, 20, 30, 20, 10]);
        $Object->setAlpha(0.75);
        $Object->calculate();

        $this->assertEquals([
            10,
            17.5,
            26.875,
            21.71875,
            12.9296875
        ], $Object->movingAverage());
    }

    public function testSimpleExampleWithAlpha25percent()
    {
        $Object = new Exponential([10, 20, 30, 20, 10]);
        $Object->setAlpha(0.25);
        $Object->calculate();

        $this->assertEquals([
            10,
            12.5,
            16.875,
            17.65625,
            15.7421875
        ], $Object->movingAverage());
    }

    /**
     * @expectedException \Exception
     */
    public function testWithIndexData()
    {
        $Object = new Exponential([1, 2, 3], [1, 2, 3]);
        $Object->calculate();
    }
}
