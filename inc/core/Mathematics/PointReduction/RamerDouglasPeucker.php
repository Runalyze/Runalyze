<?php

namespace Runalyze\Mathematics\PointReduction;

/**
 * @see https://en.wikipedia.org/wiki/Ramer%E2%80%93Douglas%E2%80%93Peucker_algorithm
 * @see http://karthaus.nl/rdp/
 */
class RamerDouglasPeucker extends AbstractPointReductionAlgorithm
{
    /** @var float */
    protected $Epsilon;

    /**
     * @param array $x
     * @param array $y
     * @param float $epsilon
     */
    public function __construct(array $x, array $y, $epsilon)
    {
        if (count($x) != count($y)) {
            throw new \InvalidArgumentException('Arrays must be of same size.');
        }

        $this->Epsilon = $epsilon;

        $this->ReducedIndices = [0, count($x) - 1];
        list($this->ReducedX, $this->ReducedY) = $this->reducePointSet($x, $y);

        sort($this->ReducedIndices);
    }

    /**
     * @param array $x
     * @param array $y
     * @param int $offset
     * @return array [reduced_x[], reduced_y[]]
     */
    protected function reducePointSet(array $x, array $y, $offset = 0)
    {
        $num = count($x);

        list($maxDist, $index) = $this->findMaximalDistanceAndIndexInPointSet($x, $y);

        if ($maxDist > $this->Epsilon) {
            $this->ReducedIndices[] = $offset + $index;

            return $this->splitAndReducePointSet($x, $y, $index, $offset);
        }

        return [
            [$x[0], $x[$num - 1]],
            [$y[0], $y[$num - 1]]
        ];
    }

    /**
     * @param array $x
     * @param array $y
     * @return array [max dist, index]
     */
    protected function findMaximalDistanceAndIndexInPointSet(array $x, array $y)
    {
        $maxDist = 0;
        $index = 0;
        $num = count($x);

        for ($i = 1; $i < $num - 1; $i++) {
            $dist = static::perpendicularDistance($x[$i], $y[$i], $x[0], $y[0], $x[$num - 1], $y[$num - 1]);

            if ($dist > $maxDist) {
                $index = $i;
                $maxDist = $dist;
            }
        }

        return [$maxDist, $index];
    }

    /**
     * @param array $x
     * @param array $y
     * @param int $index
     * @param int $offset
     * @return array [x_reduced[], y_reduced[]]
     */
    protected function splitAndReducePointSet(array $x, array $y, $index, $offset)
    {
        $num = count($x);

        list($firstX, $firstY) = $this->reducePointSet(
            array_slice($x, 0, $index + 1),
            array_slice($y, 0, $index + 1),
            $offset
        );
        list($secondX, $secondY) = $this->reducePointSet(
            array_slice($x, $index, $num - $index),
            array_slice($y, $index, $num - $index),
            $offset + $index
        );

        array_pop($firstX);
        array_pop($firstY);

        return [
            array_merge($firstX, $secondX),
            array_merge($firstY, $secondY)
        ];
    }
}
