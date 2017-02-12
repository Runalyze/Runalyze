<?php

namespace Runalyze\Tests\Mathematics\Distribution;

use Runalyze\Mathematics\Distribution\EmpiricalDistribution;
use Runalyze\Mathematics\Distribution\MultipleTimeSeries;

class MultipleTimeSeriesTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleExample()
    {
        $object = new MultipleTimeSeries();
        $object->generateDistributionsFor([
            'foo' => [10, 15, 10, 15, 10],
            'bar' => [1, 2, 3, 2, 1]
        ], [1, 2, 8, 9, 10]);

        $this->assertInstanceOf(EmpiricalDistribution::class, $object->getDistribution('foo'));
        $this->assertInstanceOf(EmpiricalDistribution::class, $object->getDistribution('bar'));
        $this->assertEquals(11, $object->getDistribution('foo')->mean());
        $this->assertEquals(2.4, $object->getDistribution('bar')->mean());
    }

    public function testAskingForUnknownDistribution()
    {
        $object = new MultipleTimeSeries();
        $object->generateDistributionsFor([
            'foo' => [10, 20, 15]
        ], [1, 2, 3]);

        $this->setExpectedException(\InvalidArgumentException::class);

        $object->getDistribution('bar');
    }

    public function testOnlyTimeSeriesGiven()
    {
        $object = new MultipleTimeSeries();
        $object->generateDistributionsFor([], [1, 2, 3]);
    }
}
