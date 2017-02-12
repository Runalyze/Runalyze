<?php

namespace Runalyze\Tests\Mathematics\Distribution;

use Runalyze\Mathematics\Distribution\EmpiricalDistribution;

class EmpiricalDistributionTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleArray()
    {
        $dist = new EmpiricalDistribution([10, 15, 15, 20]);

        $this->assertEquals([
            10 => 1,
            15 => 2,
            20 => 1
        ], $dist->histogram());
    }
}
