<?php

namespace Runalyze\Tests\Mathematics\Filter\Butterworth;

use Runalyze\Mathematics\Filter\Butterworth\ButterworthFilter;
use Runalyze\Mathematics\Filter\Butterworth\Lowpass2ndOrderCoefficients;

class ButterworthFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testOneExample()
    {
        $filter = new ButterworthFilter(
            new Lowpass2ndOrderCoefficients(0.05)
        );

        $expected = [
            11.1, 11.7, 12.4, 13.0, 13.7, 14.7, 16.1, 17.7, 19.4, 20.9, 22.2, 23.9, 26.3, 29.7, 33.3, 36.7, 39.6, 41.9, 43.5
        ];
        $actual = $filter->filterFilter([
            10.0, 12.0, 15.0, 13.0, 14.0, 11.0, 16.0, 18.0, 21.0, 25.0, 20.0, 28.0, 11.0, 35.0, 37.0, 39.0, 36.0, 40.0, 42.0
        ]);

        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals(
                $expected[$i], $actual[$i],
                sprintf('Actual values dot not match at index %u.', $i),
                0.05
            );
        }
    }
}
