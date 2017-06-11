<?php

namespace Runalyze\Mathematics\PointReduction;

abstract class AbstractPointReductionAlgorithm
{
    /** @var array */
    protected $ReducedX = [];

    /** @var array */
    protected $ReducedY = [];

    /** @var array */
    protected $ReducedIndices = [];

    /**
     * @return array
     */
    public function getReducedX()
    {
        return $this->ReducedX;
    }

    /**
     * @return array
     */
    public function getReducedY()
    {
        return $this->ReducedY;
    }

    /**
     * @return array
     */
    public function getReducedIndices()
    {
        return $this->ReducedIndices;
    }

    /**
     * @param float $pointX
     * @param float $pointY
     * @param float $line1x
     * @param float $line1y
     * @param float $line2x
     * @param float $line2y
     * @return float
     */
    public static function perpendicularDistance($pointX, $pointY, $line1x, $line1y, $line2x, $line2y)
    {
        if ($line2x == $line1x) {
            return abs($pointX - $line2x);
        }

        $slope = ($line2y - $line1y) / ($line2x - $line1x);
        $passThroughY = -$line1x * $slope + $line1y;

        return (abs(($slope * $pointX) - $pointY + $passThroughY)) / (sqrt($slope*$slope + 1));
    }
}
