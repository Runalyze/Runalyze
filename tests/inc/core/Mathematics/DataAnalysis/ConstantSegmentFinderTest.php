<?php

namespace Runalyze\Tests\Mathematics\DataAnalysis;

use Runalyze\Mathematics\DataAnalysis\ConstantSegmentFinder;

class ConstantSegmentFinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
	public function testInvalidIndexData()
    {
		new ConstantSegmentFinder([1, 2, 3], [1, 2, 3, 4, 5]);
	}

	public function testEmptyData()
    {
        $finder = new ConstantSegmentFinder([]);

        $this->assertEquals([], $finder->findConstantSegments());
    }

    public function testSimpleExample()
    {
        $finder = new ConstantSegmentFinder([1, 2, 2, 2, 3, 4, 4, 5, 5, 1]);

        $this->assertEquals([
            [1, 3],
            [5, 6],
            [7, 8]
        ], $finder->findConstantSegments());
    }

    public function testSimpleExampleWithConstantEnd()
    {
        $finder = new ConstantSegmentFinder([1, 2, 2, 2]);

        $this->assertEquals([
            [1, 3]
        ], $finder->findConstantSegments());
    }

    public function testSimpleExampleWithDelta()
    {
        $finder = new ConstantSegmentFinder([1, 2, 2, 2, 3, 5, 4, 4, 7]);
        $finder->setConstantDelta(1);

        $this->assertEquals([
            [0, 3],
            [5, 7]
        ], $finder->findConstantSegments());
    }

    public function testSimpleExampleWithDeltaAndMinimumIndexDiff()
    {
        $finder = new ConstantSegmentFinder([1, 2, 3, 3, 0, 5, 4, 3, 3, 7]);
        $finder->setConstantDelta(1);
        $finder->setMinimumIndexDiff(2);

        $this->assertEquals([
            [1, 3],
            [6, 8]
        ], $finder->findConstantSegments());
    }

    public function testSimpleExampleWithDeltaAndMinimumAndMaximumIndexDiff()
    {
        $finder = new ConstantSegmentFinder([1, 1, 1, 1, 2, 2, 5, 6, 5, 6, 5, 4]);
        $finder->setConstantDelta(1);
        $finder->setMinimumIndexDiff(2);

        $this->assertEquals([
            [0, 5],
            [6, 10]
        ], $finder->findConstantSegments());

        $finder->setMaximumIndexDiff(3);

        $this->assertEquals([
            [0, 3],
            [6, 9]
        ], $finder->findConstantSegments());
    }
}
