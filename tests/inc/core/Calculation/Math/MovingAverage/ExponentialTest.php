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

    public function testWithIndexDataEvenlySpaced()
    {
        $Indexed = new Exponential(
            [10, 20, 30, 20, 10],
            [1, 2, 3, 4, 5]
        );
        $Indexed->setAlpha(0.25);
        $Indexed->calculate();

        $NotIndexed = new Exponential(
            [10, 20, 30, 20, 10]
        );
        $NotIndexed->setAlpha(0.25);
        $NotIndexed->calculate();

        $this->assertEquals($NotIndexed->movingAverage(), $Indexed->movingAverage());
    }

    public function testWithIndexDataUnevenlySpaced()
    {
        $Object = new Exponential(
            [10, 20, 30, 20, 10],
            [1, 5, 6, 9, 10]
        );
        $Object->setAlpha(0.25);
        $Object->calculate();

        $result = $Object->movingAverage();
        $this->assertEquals(10.000, $result[0], '', 0.001);
        $this->assertEquals(16.836, $result[1], '', 0.001);
        $this->assertEquals(20.127, $result[2], '', 0.001);
        $this->assertEquals(20.053, $result[3], '', 0.001);
        $this->assertEquals(17.540, $result[4], '', 0.001);
    }
}
