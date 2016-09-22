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

    /** @expectedException \InvalidArgumentException */
    public function testRequestingNotGeneratedQuantile()
    {
        $dist = new Distribution_MockTester([]);
        $dist->quantile(0.1);
    }

    public function testQuantiles()
    {
        $dist = new Distribution_MockTester([
            10 => 1,
            20 => 5,
            30 => 2,
            40 => 2
        ]);
        $dist->calculateStatistic([0.1, 0.2, 0.5, 0.75, 0.8, 0.9]);

        $this->assertEquals(10, $dist->quantile(0.1));
        $this->assertEquals(20, $dist->quantile(0.2));
        $this->assertEquals(20, $dist->quantile(0.5));
        $this->assertEquals(30, $dist->quantile(0.75));
        $this->assertEquals(30, $dist->quantile(0.8));
        $this->assertEquals(40, $dist->quantile(0.9));
    }
}
