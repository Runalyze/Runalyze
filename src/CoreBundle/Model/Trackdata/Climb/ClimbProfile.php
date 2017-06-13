<?php

namespace Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb;

class ClimbProfile implements \Countable
{
    /** @var float [m] */
    const DEFAULT_SEGMENT_LENGTH = 0.2;

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

    /**
     * @param array $distances
     * @param array $elevations
     * @param float $segmentLength
     * @return ClimbProfile
     */
    public static function getClimbProfileFor(array $distances, array $elevations, $segmentLength = self::DEFAULT_SEGMENT_LENGTH)
    {
        $profile = new self();

        $num = count($distances);
        $lastIndex = 0;
        $currentIndex = 1;

        if (count($elevations) != $num) {
            throw new \InvalidArgumentException('Arrays must be of same size.');
        }

        while ($currentIndex < $num) {
            if ($distances[$currentIndex] - $distances[$lastIndex] >= $segmentLength) {
                $profile->addSegment($distances[$currentIndex] - $distances[$lastIndex], $elevations[$currentIndex] - $elevations[$lastIndex]);
                $lastIndex = $currentIndex;
            }

            $currentIndex++;
        }

        if ($currentIndex > $lastIndex + 1 && $distances[$currentIndex - 1] - $distances[$lastIndex] > 0.0) {
            $profile->addSegment($distances[$currentIndex - 1] - $distances[$lastIndex], $elevations[$currentIndex - 1] - $elevations[$lastIndex]);
        }

        return $profile;
    }
}
