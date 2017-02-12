<?php

namespace Runalyze\Tests\Mathematics\Distribution;

use Runalyze\Mathematics\Distribution\TimeSeries;

class TimeSeriesTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleArray()
    {
        $dist = new TimeSeries(
            [10, 15, 20, 15],
            [1, 3, 10, 13]
        );

        $this->assertEquals([
            10 => 1,
            15 => 5,
            20 => 7
        ], $dist->histogram());
    }
}
