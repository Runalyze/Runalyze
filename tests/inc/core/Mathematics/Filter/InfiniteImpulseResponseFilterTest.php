<?php

namespace Runalyze\Tests\Mathematics\Filter;

use Runalyze\Mathematics\Filter\InfiniteImpulseResponseFilter;

class InfiniteImpulseResponseFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidCoefficients()
    {
        new InfiniteImpulseResponseFilter([1, 2], [1, 2, 3]);
    }

    public function testSimpleFilter()
    {
        $filter = new InfiniteImpulseResponseFilter(
            [1.0, 0.0],
            [2.0, -1.0]
        );

        $this->assertEquals(
            [10.0, 11.0, 15.5, 12.75, 26.375, 23.1875],
            $filter->filter([10, 12, 20, 10, 40, 20])
        );
    }

    public function testSimpleFilterEnlarged()
    {
        $filter = new InfiniteImpulseResponseFilter(
            [0.5, 0.5],
            [1.0, 0.0]
        );

        $this->assertEquals(
            [2.0, 2.0, 3.0, 7.0, 10.0],
            $filter->filter([2, 4, 10], true)
        );
    }

    public function testForwardAndBackwardFilter()
    {
        $filter = new InfiniteImpulseResponseFilter(
            [0.5, 0.5],
            [1.0, 0.0]
        );

        $this->assertEquals(
            [2.5, 5.0, 8.5],
            $filter->filterFilter([2, 4, 10])
        );
    }
}
