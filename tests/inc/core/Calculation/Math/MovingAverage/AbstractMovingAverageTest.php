<?php

namespace Runalyze\Calculation\Math\MovingAverage;

class AbstractMovingAverage_MockTester extends AbstractMovingAverage
{
    public function calculateWithIndexData() { $this->MovingAverage = $this->IndexData; }
    public function calculateWithoutIndexData() { $this->MovingAverage = $this->Data; }
}

class AbstractMovingAverageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongConstruction()
    {
        new AbstractMovingAverage_MockTester([1, 2, 3], [1, 2]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidAt()
    {
        $Object = new AbstractMovingAverage_MockTester([1, 2, 3]);
        $Object->at(1);
    }

    public function testWithIndexData()
    {
        $Object = new AbstractMovingAverage_MockTester([1, 2, 3], [10, 20, 30]);
        $Object->calculate();

        $this->assertEquals([10, 20, 30], $Object->movingAverage());
    }

    public function testWithNonAscendingIndexData()
    {
        $Object = new AbstractMovingAverage_MockTester([1, 2, 3], [10, 10, 10], false);
        $Object->calculate();

        $this->assertEquals([10, 20, 30], $Object->movingAverage());
    }

    public function testWithoutIndexData()
    {
        $Object = new AbstractMovingAverage_MockTester([1, 2, 3]);
        $Object->calculate();

        $this->assertEquals([1, 2, 3], $Object->movingAverage());
        $this->assertEquals(1, $Object->at(0));
        $this->assertEquals(2, $Object->at(1));
        $this->assertEquals(3, $Object->at(2));
    }
}
