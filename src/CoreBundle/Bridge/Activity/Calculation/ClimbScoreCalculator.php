<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb\Climb;
use Runalyze\Sports\ClimbQuantification\FietsIndex;
use Runalyze\Sports\ClimbScore\ClimbScore;

class ClimbScoreCalculator
{
    /** @var float [m/m] */
    const CLIMB_GRADIENT_THRESHOLD = 0.02;

    /** @var float [km] */
    const CLIMB_DISTANCE_THRESHOLD = 0.1;

    public function calculateFor(Training $activity)
    {
        $score = new ClimbScore();

        if ($activity->hasRoute()) {
            $score->setScoreFromClassifiedClimbs(
                $this->getFietsIndicesFor($activity),
                $activity->getRoute()->getDistance(),
                0.4248551385428408 //$activity->getPercentageFlat()
            );
        }

        //$activity->setClimbScore($score);

        return $score;
    }

    /**
     * @param Training $activity
     * @return array
     */
    protected function getFietsIndicesFor(Training $activity)
    {
        $fietsIndex = new FietsIndex();
        $climbs = (new ClimbFinder())->findClimbsFor($activity);

        $fietsIndices = array_map(function (Climb $climb) use ($fietsIndex) {
            if ($climb->getGradient() < self::CLIMB_GRADIENT_THRESHOLD || $climb->getDistance() <= self::CLIMB_DISTANCE_THRESHOLD) {
                return 0;
            }

            if ($climb->knowsClimbProfile()) {
                return $fietsIndex->getScoreForProfile($climb->getClimbProfile()->getDistancesWithGradients(), $climb->getAltitudeAtTop());
            }

            return $fietsIndex->getScoreFor($climb->getDistance(), $climb->getElevation(), (int)$climb->getAltitudeAtTop());
        }, $climbs->getElements());

        return $fietsIndices;
    }
}
