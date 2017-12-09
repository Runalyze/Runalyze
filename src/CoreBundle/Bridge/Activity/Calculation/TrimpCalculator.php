<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Athlete;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Calculation\Trimp\Calculator;
use Runalyze\Calculation\Trimp\DataCollector;

class TrimpCalculator
{
    /** @var int */
    protected $MaximalTrimp;

    /** @var int */
    protected $MaximalTrimpPerHour;

    /**
     * @param int $maximalTrimp
     * @param int $maximalTrimpPerHour
     */
    public function __construct($maximalTrimp = 10000, $maximalTrimpPerHour = 400)
    {
        $this->MaximalTrimp = $maximalTrimp;
        $this->MaximalTrimpPerHour = $maximalTrimpPerHour;
    }

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

        $value = (int)$calculator->value();

        $this->checkIfValueIsOutOfRange($value, $activity);

        $activity->setTrimp($value);
    }

    /**
     * @param Training $activity
     * @return array
     */
    protected function getHeartRateHistogram(Training $activity)
    {
        if ($activity->hasTrackdata() && $activity->getTrackdata()->hasHeartrate() && $activity->getTrackdata()->hasTime()) {
            return (new DataCollector($activity->getTrackdata()->getHeartrate(), $activity->getTrackdata()->getTime()))->result();
        }

        return [$activity->getAdapter()->getAverageHeartRateWithFallbackToTypeOrSport() => $activity->getS()];
    }

    /**
     * @param int|null $value
     * @param Training $activity
     */
    protected function checkIfValueIsOutOfRange(&$value, Training $activity)
    {
        if (null !== $value) {
            if ($value > $this->MaximalTrimp) {
                $value = null;
            } elseif ($activity->getS() > 0 && $value / ($activity->getS() / 3600) > $this->MaximalTrimpPerHour) {
                $value = null;
            }
        }
    }
}
