<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb\Climb;
use Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb\ClimbCollection;
use Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb\ClimbProfile;
use Runalyze\Mathematics\PointReduction\RamerDouglasPeucker;

class ClimbFinder
{
    /** @var float [-] */
    const EPSILON_DEFAULT = 1.1;

    /** @var float [m] */
    const EPSILON_FOR_CLIMB_PROFILE = 0.1;

    /**
     * @param Training $activity
     * @param float $epsilon
     * @return ClimbCollection
     */
    public function findClimbsFor(Training $activity, $epsilon = self::EPSILON_DEFAULT)
    {
        if ($activity->hasRoute() && $activity->hasTrackdata() && null !== $activity->getTrackdata()->getDistance()) {
            $distances = $activity->getTrackdata()->getDistance();
            $elevations = $activity->getRoute()->getElevations();
            $cuttingIndices = (new RamerDouglasPeucker($distances, $elevations, $epsilon))->getReducedIndices();

            return $this->createClimbsForIndices($cuttingIndices, $distances, $elevations);
        }

        return new ClimbCollection();
    }

    /**
     * @param array $indices
     * @param array $distances
     * @param array $elevations
     * @return ClimbCollection
     */
    protected function createClimbsForIndices(array $indices, array $distances, array $elevations)
    {
        $numIndices = count($indices);
        $climbs = new ClimbCollection();

        for ($i = 0; $i < $numIndices - 1; ++$i) {
            $index = $indices[$i];
            $endIndex = $indices[$i + 1];

            if ($elevations[$endIndex] > $elevations[$index]) {
                $climb = new Climb(
                    $distances[$endIndex] - $distances[$index],
                    $elevations[$endIndex] - $elevations[$index],
                    $index,
                    $endIndex
                );
                $climb->setAltitudeAtTop($elevations[$endIndex]);
                $climb->setClimbProfile($this->getClimbProfileFor(
                    array_slice($distances, $index, $endIndex - $index + 1),
                    array_slice($elevations, $index, $endIndex - $index + 1)
                ));

                $climbs->add($climb);
            }
        }

        return $climbs;
    }

    /**
     * @param array $distances
     * @param array $elevations
     * @return ClimbProfile
     */
    protected function getClimbProfileFor(array $distances, array $elevations)
    {
        return ClimbProfile::getClimbProfileFor($distances, $elevations, self::EPSILON_FOR_CLIMB_PROFILE);
    }
}
