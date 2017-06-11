<?php

namespace Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb;

class ClimbProfile implements \Countable
{
    /** @var float[] [km] */
    protected $Distances;

    /** @var int[] [m] */
    protected $Elevations;

    /**
     * @param float[] $distances [km]
     * @param int[] $elevations [m]
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $distances = [], array $elevations = [])
    {
        if (count($distances) != count($elevations)) {
            throw new \InvalidArgumentException('Distances and elevations must be of same size.');
        }

        $this->Distances = $distances;
        $this->Elevations = $elevations;
    }

    /**
     * @param float $distance [km]
     * @param int $elevation [m]
     *
     * @throws \InvalidArgumentException
     */
    public function addSegment($distance, $elevation)
    {
        if ($distance <= 0.0) {
            throw new \InvalidArgumentException('Distance must be positive.');
        }

        $this->Distances[] = $distance;
        $this->Elevations[] = $elevation;
    }

    public function count()
    {
        return count($this->Distances);
    }

    /**
     * @return float[] [km]
     */
    public function getDistances()
    {
        return $this->Distances;
    }

    /**
     * @return int[] [m]
     */
    public function getElevations()
    {
        return $this->Elevations;
    }

    /**
     * @return float[] [0.00 .. 1.00]
     */
    public function getGradients()
    {
        return array_map(function ($distance, $elevation) {
            return $elevation / $distance / 1000;
        }, $this->Distances, $this->Elevations);
    }

    /**
     * @return array[] [[distance in km, gradient in 0.00 .. 1.00], ...]
     */
    public function getDistancesWithGradients()
    {
        return array_map(function ($distance, $elevation) {
            return [$distance, $elevation / $distance / 1000];
        }, $this->Distances, $this->Elevations);
    }
}
