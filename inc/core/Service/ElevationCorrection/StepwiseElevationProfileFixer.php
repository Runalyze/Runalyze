<?php

namespace Runalyze\Service\ElevationCorrection;

class StepwiseElevationProfileFixer
{
    /** @var int */
    const METHOD_FIXED_GROUP_SIZE = 0;

    /** @var int */
    const METHOD_VARIABLE_GROUP_SIZE = 1;

    /** @var int */
    protected $Method;

	/** @var int */
	protected $NumberOfGroupedPoints;

	/** @var int */
	protected $MidpointIndexOfConstantGroup;

	public function __construct($numberOfGroupedPoints = 5, $method = self::METHOD_FIXED_GROUP_SIZE)
    {
        $this->NumberOfGroupedPoints = $numberOfGroupedPoints;
        $this->MidpointIndexOfConstantGroup = (int)floor(($numberOfGroupedPoints - 1) / 2);
        $this->Method = $method;
    }

    /**
     * Smooth elevation profile if it's composed of constant groups
     *
     * @param array $elevations
     * @param array $distances
     * @return array
     */
    public function fixStepwiseElevations(array $elevations = null, array $distances = [])
    {
        if ($this->isProfileStepwise($elevations)) {
            $numElevations = count($elevations);

            if (empty($distances)) {
                $distances = range(0.0, $numElevations - 1.0);
            }

            if (self::METHOD_FIXED_GROUP_SIZE == $this->Method) {
                $midpoints = $this->getMidpointsForFixedGroupSize($numElevations);
            } else {
                $midpoints = $this->findMidpointsForConstantParts($elevations);
            }

            $elevations = $this->fixStepwiseElevationsForMidpoints($elevations, $distances, $midpoints);
        }

        return $elevations;
    }

    /**
     * @param int $arraySize
     * @return array
     */
    protected function getMidpointsForFixedGroupSize($arraySize)
    {
        if ($this->NumberOfGroupedPoints >= $arraySize) {
            return [$this->MidpointIndexOfConstantGroup];
        }

        return range($this->MidpointIndexOfConstantGroup, $arraySize - 1, $this->NumberOfGroupedPoints);
    }

    /**
     * @param array $elevations
     * @return array
     */
    protected function findMidpointsForConstantParts(array $elevations)
    {
        $midpoints = [];
        $lastDiffIndex = -1;
        $numElevations = count($elevations);
        $currentElevation = $elevations[0];

        for ($i = 1; $i < $numElevations - 1; ++$i) {
            if ($elevations[$i] != $currentElevation) {
                $midpoints[] = floor(($lastDiffIndex + $i) / 2);

                $lastDiffIndex = $i - 1;
                $currentElevation = $elevations[$i];
            }
        }

        if ($elevations[$numElevations - 1] == $elevations[$numElevations - 2]) {
            $midpoints[] = floor(($lastDiffIndex + $numElevations - 1) / 2);
        }

        return $midpoints;
    }

    /**
     * @param array $elevations
     * @param array $distances
     * @param array $midpoints
     * @return array
     */
    protected function fixStepwiseElevationsForMidpoints(array $elevations, array $distances, array $midpoints)
    {
        $numMidpoints = count($midpoints);

        for ($i = 0; $i < $numMidpoints - 1; ++$i) {
            for ($j = $midpoints[$i] + 1; $j < $midpoints[$i + 1]; ++$j) {
                if ($distances[$midpoints[$i + 1]] != $distances[$midpoints[$i]]) {
                    $partial = ($distances[$j] - $distances[$midpoints[$i]]) / ($distances[$midpoints[$i + 1]] - $distances[$midpoints[$i]]);
                    $elevations[$j] = round((1.0 - $partial) * $elevations[$midpoints[$i]] + $partial * $elevations[$midpoints[$i + 1]]);
                }
            }
        }

        return $elevations;
    }

    /**
     * @param array $elevations
     * @return bool
     */
    public function isProfileStepwise(array $elevations)
    {
        $num = count($elevations);

        for ($i = 1; $i < $num; ++$i) {
            if (0 != $i % $this->NumberOfGroupedPoints && $elevations[$i] != $elevations[$i - 1]) {
                return false;
            }
        }

        return $num > $this->NumberOfGroupedPoints;
    }
}
