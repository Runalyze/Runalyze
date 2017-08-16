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

    /**
     * @param float $pointX
     * @param float $pointY
     * @param float $line1x
     * @param float $line1y
     * @param float $line2x
     * @param float $line2y
     * @return float
     */
    public static function shortestDistance($pointX, $pointY, $line1x, $line1y, $line2x, $line2y)
    {
        $lineLength = self::pointDistance($line1x, $line1y, $line2x, $line2y);

        if (0 == $lineLength) {
            return self::pointDistance($pointX, $pointY, $line1x, $line1y);
        }

        $t = (($pointX - $line1x) * ($line2x - $line1x) + ($pointY - $line1y) * ($line2y - $line1y)) / ($lineLength * $lineLength);

        if ($t < 0) {
            return self::pointDistance($pointX, $pointY, $line1x, $line1y);
        } else if ($t > 1) {
            return self::pointDistance($pointX, $pointY, $line2x, $line2y);
        }

        return self::pointDistance($pointX, $pointY, $line1x + $t * ($line2x - $line1x), $line1y + $t * ($line2y - $line1y));
    }

    /**
     * @param float $point1x
     * @param float $point1y
     * @param float $point2x
     * @param float $point2y
     * @return float
     */
    public static function pointDistance($point1x, $point1y, $point2x, $point2y)
    {
        return sqrt(($point2x - $point1x) * ($point2x - $point1x) + ($point2y - $point1y) * ($point2y - $point1y));
    }
}
