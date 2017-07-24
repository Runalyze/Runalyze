<?php

namespace Runalyze\Tests\Mathematics\Interpolation\BSpline;

use Runalyze\Mathematics\Interpolation\BSpline\BSplineInterpolation;

class BSplineInterpolationTest extends \PHPUnit_Framework_TestCase
{
    protected function assertArraysAreSimilar(array $expected, array $given, $precision)
    {
        $this->assertSameSize($expected, $given);

        $num = count($expected);

        for ($i = 0; $i < $num; ++$i) {
            $this->assertEquals($expected[$i], $given[$i], '', $precision);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidDegree()
    {
        new BSplineInterpolation(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonNumericDegree()
    {
        new BSplineInterpolation('foo');
    }

    public function testEvaluationOnEmptyCurve()
    {
        $spline = new BSplineInterpolation(2);

        $this->setExpectedException(\RuntimeException::class);

        $spline->evaluateAt(0.5);
    }

    public function testNotEnoughPoints()
    {
        $spline = new BSplineInterpolation(2);

        $this->setExpectedException(\InvalidArgumentException::class);

        $spline->setPoints([1, 2]);
    }

    public function testConstantCurve()
    {
        $spline = new BSplineInterpolation(5);
        $spline->setPoints([42, 42, 42, 42, 42, 42, 42, 42, 42, 42]);

        $this->assertEquals([42, 42, 42, 42, 42, 42, 42, 42, 42, 42], $spline->evaluateAtAllPoints());
    }

    public function testLinearCurve()
    {
        $spline = new BSplineInterpolation(1);
        $spline->setPoints([10, 20, 30, 20, 40, 30, 10]);

        $this->assertEquals([10, 20, 30, 20, 40, 30, 10], $spline->evaluateAtAllPoints());
    }

    public function testCubicCurve()
    {
        $spline = new BSplineInterpolation(2);
        $spline->setPoints([10, 20, 30, 20, 40, 30, 10]);

        $this->assertArraysAreSimilar([15, 23.3, 27.2, 23.8, 35, 33.2, 20], $spline->evaluateAtAllPoints(true), 0.1);
        $this->assertArraysAreSimilar([10, 23.2, 27.2, 23.8, 35, 32.9, 10], $spline->evaluateAtAllPoints(false), 0.1);
    }

    public function testUniformCurveFromOriginalSource()
    {
        $spline = new BSplineInterpolation(2);
        $spline->setPoints([
            [-1.0,  0.0],
            [-0.5,  0.5],
            [ 0.5, -0.5],
            [ 1.0,  0.0]
        ]);

        $this->assertArraysAreSimilar([
            [-0.75, 0.25],
            [-0.64, 0.32],
            [-0.51, 0.33],
            [-0.36, 0.28],
            [-0.19, 0.17],
            [0.00, 0.00],
            [0.19, -0.17],
            [0.36, -0.28],
            [0.51, -0.33],
            [0.64, -0.32],
            [0.75, -0.25]
        ], $spline->evaluateAt([0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0]), 0.01);
    }

    public function testNonUniformCurveFromOriginalSource()
    {
        $spline = new BSplineInterpolation(2);
        $spline->setPoints([
            [-1.0,  0.0],
            [-0.5,  0.5],
            [ 0.5, -0.5],
            [ 1.0,  0.0]
        ], [], [0, 0, 0, 1, 2, 2, 2]);

        $this->assertArraysAreSimilar([
            [-1.0, 0.00],
            [-0.8, 0.16],
            [-0.6, 0.24],
            [-0.4, 0.24],
            [-0.2, 0.16],
            [0.0, 0.00],
            [0.2, -0.16],
            [0.4, -0.24],
            [0.6, -0.24],
            [0.8, -0.16],
            [1.0, -0.00]
        ], $spline->evaluateAt([0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0]), 0.1);
    }

    public function testNonUniformRationalCurveFromOriginalSource()
    {
        $w = pow(0.5, 0.5);
        $spline = new BSplineInterpolation(2);
        $spline->setPoints([
            [0.0, -0.5],
            [-0.5, -0.5],
            [-0.5, 0.0],
            [-0.5, 0.5],
            [0.0, 0.5],
            [0.5, 0.5],
            [0.5, 0.0],
            [0.5, -0.5],
            [0.0, -0.5]
        ], [1, $w, 1, $w, 1, $w, 1, $w, 1], [0, 0, 0, 1/4, 1/4, 1/2, 1/2, 3/4, 3/4, 1, 1, 1]);

        $this->assertArraysAreSimilar([
            [0.0, -0.5],
            [-0.2906, -0.4069],
            [-0.4779, -0.1469],
            [-0.4779, 0.1469],
            [-0.2906, 0.4069],
            [0.0, 0.5],
            [0.2906, 0.4069],
            [0.4779, 0.1469],
            [0.4779, -0.1469],
            [0.2906, -0.4069],
            [0.0, -0.5]
        ], $spline->evaluateAt([0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0]), 0.0001);
    }
}
