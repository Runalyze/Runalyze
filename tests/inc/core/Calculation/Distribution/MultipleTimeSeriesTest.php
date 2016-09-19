<?php

namespace Runalyze\Calculation\Distribution;

class MultipleTimeSeriesTest extends \PHPUnit_Framework_TestCase
{
	public function testSimpleExample()
    {
		$object = new MultipleTimeSeries([
            'foo' => [10, 15, 10, 15, 10],
            'bar' => [1, 2, 3, 2, 1]
        ], [1, 2, 8, 9, 10]);

        $this->assertInstanceOf(Empirical::class, $object->getDistribution('foo'));
        $this->assertInstanceOf(Empirical::class, $object->getDistribution('bar'));
        $this->assertEquals(11, $object->getDistribution('foo')->mean());
        $this->assertEquals(2.4, $object->getDistribution('bar')->mean());
	}

    /** @expectedException \InvalidArgumentException */
    public function testAskingForUnknownDistribution()
    {
        $object = new MultipleTimeSeries([
            'foo' => [10, 20, 15]
        ], [1, 2, 3]);

        $object->getDistribution('bar');
    }

    public function testNoTimeSeriesGiven()
    {
        new MultipleTimeSeries([], [1, 2, 3]);
    }
}
