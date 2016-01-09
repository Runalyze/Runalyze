<?php

namespace Runalyze\Calculation\Math\MovingAverage;

class CumulativeTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleExample()
    {
        $Object = new Cumulative([5, 3, 4, 8, 10, 6, -1]);
        $Object->calculate();

        $this->assertEquals([5, 4, 4, 5, 6, 6, 5], $Object->movingAverage());
    }

    public function testWithTrivialIndexData()
    {
        $Object = new Cumulative([0, 1, 2], [1, 1, 1], false);
        $Object->calculate();

        $this->assertEquals([0, 0.5, 1], $Object->movingAverage());
    }

    public function testWithIndexData()
    {
        $Object = new Cumulative([6, 12, 14, 15], [1, 2, 1, 4], false);
        $Object->calculate();

        $this->assertEquals([6, 10, 11, 13], $Object->movingAverage());
    }
}
