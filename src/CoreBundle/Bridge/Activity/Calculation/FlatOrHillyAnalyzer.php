<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb\ClimbProfile;

class FlatOrHillyAnalyzer
{
    /** @var float */
    const DEFAULT_GRADIENT_THRESHOLD = 0.02;

    /**
     * @param Training $activity
     * @param float $threshold
     * @return float|null
     */
    public function calculatePercentageFlatFor(Training $activity, $threshold = self::DEFAULT_GRADIENT_THRESHOLD)
    {
        $percentageFlat = null;

        if ($activity->hasRoute() && $activity->getRoute()->hasElevations() && $activity->hasTrackdata() && null !== $activity->getTrackdata()->getDistance()) {
            $percentageFlat = $this->calculatePercentageFlatForArrays(
                $activity->getTrackdata()->getDistance(),
                $activity->getRoute()->getElevations(),
                $threshold
            );
        }

        $activity->setPercentageFlat($percentageFlat);

        return $percentageFlat;
    }

    /**
     * @param float[] $distances
     * @param int[] $elevations
     * @param float $threshold
     * @return float|null
     */
    public function calculatePercentageFlatForArrays(array $distances, array $elevations, $threshold = self::DEFAULT_GRADIENT_THRESHOLD)
    {
        if (empty($distances)) {
            return null;
        }

        $distanceFlat = 0.0;
        $distanceHilly = 0.0;
        $distancesWithGradients = ClimbProfile::getClimbProfileFor($distances, $elevations)->getDistancesWithGradients();

        foreach ($distancesWithGradients as $data) {
            if ($data[1] >= $threshold || $data[1] < -$threshold) {
                $distanceHilly += $data[0];
            } else {
                $distanceFlat += $data[0];
            }
        }

        return $distanceFlat == 0.0 ? 0.0 : $distanceFlat / ($distanceFlat + $distanceHilly);
    }
}
