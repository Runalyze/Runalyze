<?php

namespace Runalyze\Mathematics\Interpolation\BSpline;

/**
 * PHP version of thibauts/b-spline
 *
 * B-spline interpolation of control points of any dimensionality using de Boor's algorithm.
 *
 * The interpolator can take an optional weight vector, making the resulting curve a
 * Non-Uniform Rational B-Spline (NURBS) curve if you wish so.
 *
 * The knot vector is optional too, and when not provided an unclamped uniform knot vector
 * will be generated internally.
 *
 * @see https://github.com/thibauts/b-spline
 */
class BSplineInterpolation
{
    /** @var int */
    protected $Degree;

    /** @var int */
    protected $NumPoints = 0;

    /** @var int */
    protected $Dimension = 0;

    /** @var array */
    protected $Points = [];

    /** @var array */
    protected $Coordinates = [];

    /** @var array */
    protected $Weights = [];

    /** @var array */
    protected $Knots = [];

    /** @var array */
    protected $Domain = [];

    /**
     * @param int $degree
     */
    public function __construct($degree)
    {
        if ((int)$degree < 1) {
            throw new \InvalidArgumentException('Degree must be at least 1 (linear).');
        }

        $this->Degree = $degree;
    }

    public function setPoints(array $points, array $weights = [], array $knots = [])
    {
        $this->NumPoints = count($points);
        $this->Dimension = count($points[0]);

        if ($this->NumPoints <= $this->Degree) {
            throw new \InvalidArgumentException('Number of points must be greater than degree.');
        }

        if (count($weights) != $this->NumPoints) {
            $weights = array_fill(0, $this->NumPoints, 1.0);
        }

        if (count($knots) != $this->NumPoints + $this->Degree + 1) {
            $knots = range(0, $this->NumPoints + $this->Degree);
        }

        if (1 == $this->Dimension) {
            $points = array_map(function ($v) {
                return [$v];
            }, $points);
        }

        $this->Points = $points;
        $this->Weights = $weights;
        $this->Knots = $knots;
        $this->Domain = [$this->Degree, $this->NumPoints];

        $this->convertPointsToHomogeneousCoordinates();
    }

    public function setClampedKnotVector()
    {
        $this->Knots = array_merge(
            array_fill(0, $this->Degree + 1, 0),
            range(1, $this->NumPoints - $this->Degree - 1),
            array_fill($this->NumPoints, $this->Degree + 1, $this->NumPoints - $this->Degree)
        );
    }

    /**
     * @param bool $keepCurrentKnots by default, knots are overwritten
     * @return array
     */
    public function evaluateAtAllPoints($keepCurrentKnots = false)
    {
        if (!$keepCurrentKnots) {
            $this->setClampedKnotVector();
        }

        $t = [];

        for ($i = 0; $i < $this->NumPoints; ++$i) {
            $t[] = $i / ($this->NumPoints - 1);
        }

        return $this->evaluateAt($t);
    }

    /**
     * @param float|array $t
     * @return array|float|int
     */
    public function evaluateAt($t)
    {
        if (empty($this->Points)) {
            throw new \RuntimeException('Points must be set first.');
        }

        if (is_array($t)) {
            return array_map(function ($v) {
                return $this->evaluateAt($v);
            }, $t);
        }

        $t = $this->remapPartialValue($t);
        $s = $this->findSplineSegment($t);
        $coordinates = $this->Coordinates;

        for ($l = 1; $l <= $this->Degree + 1; ++$l) {
            for ($i = $s; $i > $s - $this->Degree - 1 + $l; --$i) {
                $alpha = ($t - $this->Knots[$i]) / ($this->Knots[$i + $this->Degree + 1 - $l] - $this->Knots[$i]);

                for ($j = 0; $j < $this->Dimension + 1; ++$j) {
                    $coordinates[$i][$j] = (1 - $alpha) * $coordinates[$i - 1][$j] + $alpha * $coordinates[$i][$j];
                }
            }
        }

        if ($this->Dimension > 1) {
            $result = [];

            for ($i = 0; $i < $this->Dimension; ++$i) {
                $result[$i] = $coordinates[$s][$i] / $coordinates[$s][$this->Dimension];
            }
        } else {
            $result = $coordinates[$s][0] / $coordinates[$s][1];
        }

        return $result;
    }

    protected function convertPointsToHomogeneousCoordinates()
    {
        $this->Coordinates = [];

        for ($i = 0; $i < $this->NumPoints; ++$i) {
            $this->Coordinates[$i] = [];

            for ($j = 0; $j < $this->Dimension; ++$j) {
                $this->Coordinates[$i][$j] = $this->Points[$i][$j] * $this->Weights[$i];
            }

            $this->Coordinates[$i][$this->Dimension] = $this->Weights[$i];
        }
    }

    /**
     * @param float $t [0.00 .. 1.00]
     * @return float
     */
    protected function remapPartialValue($t)
    {
        $low  = $this->Knots[$this->Domain[0]];
        $high = $this->Knots[$this->Domain[1]];

        return max($low, min($high, $t * ($high - $low) + $low));
    }

    /**
     * @param float $t [0.00 .. 1.00]
     * @return int
     */
    protected function findSplineSegment($t)
    {
        for ($s = $this->Domain[0]; $s < $this->Domain[1]; ++$s) {
            if ($t >= $this->Knots[$s] && $t <= $this->Knots[$s + 1]) {
                break;
            }
        }

        return $s;
    }
}
