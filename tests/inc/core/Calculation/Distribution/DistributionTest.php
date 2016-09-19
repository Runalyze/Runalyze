<?php

namespace Runalyze\Calculation\Distribution;

class Distribution_MockTester extends Distribution
{
    /** @var array */
    protected $Histogram = [];

    public function __construct(array $histogram)
    {
        $this->Histogram = $histogram;
    }

	public function histogram()
    {
		return $this->Histogram;
	}
}

class DistributionTest extends \PHPUnit_Framework_TestCase
{
	public function testMock()
    {
		$dist = new Distribution_MockTester([
            10 => 1,
            15 => 2,
            20 => 1
        ]);

		$this->assertEquals(array(
			10 => 1,
			15 => 2,
			20 => 1
		), $dist->histogram());

		$dist->calculateStatistic();

		$this->assertEquals(10, $dist->min());
		$this->assertEquals(15, $dist->mean());
		$this->assertEquals(15, $dist->median());
		$this->assertEquals(15, $dist->mode());
		$this->assertEquals(20, $dist->max());
		$this->assertEquals(12.5, $dist->variance());
		$this->assertEquals(3.5, $dist->stdDev(), '', 0.1);
		$this->assertEquals(3.5/15, $dist->coefficientOfVariation(), '', 0.1);
	}

	public function testMock2()
    {
		$dist = new Distribution_MockTester([
            0 => 5,
            80 => 10,
            85 => 10,
            90 => 15
        ]);

		$dist->calculateStatistic();

		$this->assertEquals(0, $dist->min());
		$this->assertEquals(75, $dist->mean());
		$this->assertEquals(85, $dist->median());
		$this->assertEquals(90, $dist->mode());
		$this->assertEquals(90, $dist->max());
		$this->assertEquals(818.75, $dist->variance());
		$this->assertEquals(28.6, $dist->stdDev(), '', 0.1);
        $this->assertEquals(28.6/75, $dist->coefficientOfVariation(), '', 0.1);
	}

    public function testEmptyHistogram()
    {
        $dist = new Distribution_MockTester([]);
        $dist->calculateStatistic();

        $this->assertEquals(0, $dist->min());
        $this->assertEquals(0, $dist->mean());
        $this->assertEquals(0, $dist->median());
        $this->assertEquals(0, $dist->mode());
        $this->assertEquals(0, $dist->max());
        $this->assertEquals(0, $dist->variance());
        $this->assertEquals(0, $dist->stdDev());
        $this->assertFalse($dist->coefficientOfVariation());
    }
}
