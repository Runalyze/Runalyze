<?php

namespace Runalyze\Service\ElevationCorrection\Strategy;

abstract class AbstractStrategy implements StrategyInterface
{
    /**
     * Points to group together
     *
     * This is only a bad guess.
     * It would be better to decide this by distance between the points.
     *
     * @var int
     */
    protected $PointsToGroup = 5;

    /**
     * @param int $numberOfPoints
     */
    public function setPointsToGroup($numberOfPoints)
    {
        $this->PointsToGroup = $numberOfPoints;
    }
}
