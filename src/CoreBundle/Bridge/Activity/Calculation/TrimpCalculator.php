<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Athlete;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Calculation\Trimp\Calculator;
use Runalyze\Calculation\Trimp\DataCollector;

class TrimpCalculator
{
    /**
     * @param Training $activity
     * @param int $gender enum, see \Runalyze\Profile\Athlete\Gender
     * @param int $heartRateMaximum [bpm]
     * @param int $heartRateResting [bpm]
     */
    public function calculateFor(Training $activity, $gender, $heartRateMaximum, $heartRateResting)
    {
        $calculator = new Calculator(
            new Athlete($gender, $heartRateMaximum, $heartRateResting),
            $this->getHeartRateHistogram($activity)
        );

        $activity->setTrimp((int)$calculator->value());
    }

    /**
     * @param Training $activity
     * @return array
     */
    protected function getHeartRateHistogram(Training $activity)
    {
        if ($activity->hasTrackdata() && $activity->getTrackdata()->hasHeartrate()) {
            return (new DataCollector($activity->getTrackdata()->getHeartrate(), $activity->getTrackdata()->getTime()))->result();
        }

        return [$activity->getAdapter()->getAverageHeartRateWithFallbackToTypeOrSport() => $activity->getS()];
    }
}
