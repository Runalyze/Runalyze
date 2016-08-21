<?php

namespace Runalyze\Calculation\Math;

class SubSegmentMaximizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
	public function testEmptyInput()
    {
		new SubSegmentMaximization([], [], []);
	}

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNoSegmentLengths()
    {
        new SubSegmentMaximization([1, 1, 1], [1, 1, 1], []);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnbalancedInputSizes()
    {
        new SubSegmentMaximization([1, 1], [1, 1, 1], [1]);
    }

    public function testEnabledInterpolation()
    {
        $Maximization = new SubSegmentMaximization([42], [5], [1, 2, 3, 4, 5]);
        $Maximization->maximize();

        $this->assertEquals(8.4, $Maximization->getMaximumForLengthIndex(0));
        $this->assertEquals(16.8, $Maximization->getMaximumForLengthIndex(1));
        $this->assertEquals(25.2, $Maximization->getMaximumForLengthIndex(2));
        $this->assertEquals(33.6, $Maximization->getMaximumForLengthIndex(3));
        $this->assertEquals(42, $Maximization->getMaximumForLengthIndex(4));
    }

    public function testDisabledInterpolation()
    {
        $Maximization = new SubSegmentMaximization([42], [5], [1, 2, 3, 4, 5]);
        $Maximization->disableInterpolationForOverlength();
        $Maximization->maximize();

        $this->assertEquals(42, $Maximization->getMaximumForLengthIndex(0));
        $this->assertEquals(42, $Maximization->getMaximumForLengthIndex(1));
        $this->assertEquals(42, $Maximization->getMaximumForLengthIndex(2));
        $this->assertEquals(42, $Maximization->getMaximumForLengthIndex(3));
        $this->assertEquals(42, $Maximization->getMaximumForLengthIndex(4));
    }

    public function testSimpleExample()
    {
        $Maximization = new SubSegmentMaximization(
            [1, 2, 5, 1, 3, 9, 2, 4, 4, 1, 2, 5, 4, 6, 4, 2, 5, 4, 7, 3, 2],
            [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
            [2, 5, 10]
        );
        $Maximization->maximize();

        $this->assertEquals(12, $Maximization->getMaximumForLengthIndex(0));
        $this->assertEquals([4, 5], $Maximization->getIndizesOfMaximumForLengthIndex(0));
        $this->assertEquals(22, $Maximization->getMaximumForLengthIndex(1));
        $this->assertEquals([4, 8], $Maximization->getIndizesOfMaximumForLengthIndex(1));
        $this->assertEquals(42, $Maximization->getMaximumForLengthIndex(2));
        $this->assertEquals([10, 19], $Maximization->getIndizesOfMaximumForLengthIndex(2));
    }

    public function testSimpleExampleForMinimization()
    {
        $Maximization = new SubSegmentMaximization(
            [1, 2, 5, 1, 3, 9, 2, 4, 4, 1, 2, 5, 4, 6, 4, 2, 5, 4, 7, 3, 2],
            [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
            [2, 5, 10]
        );
        $Maximization->minimize();

        $this->assertEquals(3, $Maximization->getMaximumForLengthIndex(0));
        $this->assertEquals([0, 1], $Maximization->getIndizesOfMaximumForLengthIndex(0));
        $this->assertEquals(12, $Maximization->getMaximumForLengthIndex(1));
        $this->assertEquals([0, 4], $Maximization->getIndizesOfMaximumForLengthIndex(1));
        $this->assertEquals(32, $Maximization->getMaximumForLengthIndex(2));
        $this->assertEquals([0, 9], $Maximization->getIndizesOfMaximumForLengthIndex(2));
    }

    public function testTooShortInputData()
    {
        $Maximization = new SubSegmentMaximization(
            [1, 2, 5, 1, 3],
            [1, 1, 1, 1, 1],
            [5, 10, 30, 60]
        );
        $Maximization->maximize();

        $this->assertTrue($Maximization->hasMaximumForLengthIndex(0));
        $this->assertEquals(12, $Maximization->getMaximumForLengthIndex(0));
        $this->assertEquals([0, 4], $Maximization->getIndizesOfMaximumForLengthIndex(0));

        $this->assertFalse($Maximization->hasMaximumForLengthIndex(1));
        $this->assertFalse($Maximization->hasMaximumForLengthIndex(2));
        $this->assertFalse($Maximization->hasMaximumForLengthIndex(3));
    }
}
