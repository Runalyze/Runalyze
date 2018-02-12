<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Calculation\Elevation\DistanceModifier;
use Runalyze\Sports\Running\VO2max\Estimation\DanielsGilbertFormula;

class VO2maxCalculator
{
    /** @var Training */
    protected $Activity;

    /** @var int [bpm] */
    protected $HeartRateMaximum;

    /** @var int [m] */
    protected $CorrectionForPositiveElevation;

    /** @var int [m] */
    protected $CorrectionForNegativeElevation;

    /** @var DanielsGilbertFormula */
    protected $EstimationFormula;

    /**
     * @param Training $activity
     * @param int $heartRateMaximum [bpm]
     * @param int $correctionForPositiveElevation [m]
     * @param int $correctionForNegativeElevation [m]
     */
    public function calculateFor(
        Training $activity,
        $heartRateMaximum,
        $correctionForPositiveElevation,
        $correctionForNegativeElevation
    )
    {
        $this->Activity = $activity;
        $this->HeartRateMaximum = $heartRateMaximum;
        $this->CorrectionForPositiveElevation = $correctionForPositiveElevation;
        $this->CorrectionForNegativeElevation = $correctionForNegativeElevation;
        $this->EstimationFormula = new DanielsGilbertFormula();

        $this->Activity->setVO2maxByTime($this->estimateVO2maxByTime());
        $this->Activity->setVO2max($this->estimateVO2maxByHeartRate());
        $this->Activity->setVO2maxWithElevation($this->estimateVO2maxByHeartRateWithElevation());
    }

    /**
     * @return float [ml/kg/min]
     */
    protected function estimateVO2maxByTime()
    {
        return $this->EstimationFormula->estimateFromRaceResult($this->Activity->getDistance(), $this->Activity->getS());
    }

    /**
     * @param float|null $distance [km]
     * @return float [ml/kg/min]
     */
    protected function estimateVO2maxByHeartRate($distance = null)
    {
        $duration = $this->Activity->getS();
        $distance = $distance ?: $this->Activity->getDistance();
        $heartRateInPercent = $this->Activity->getPulseAvg() / $this->HeartRateMaximum;

        if ($heartRateInPercent <= 0.0 || $duration == 0) {
            return 0.0;
        }

        $speedReallyAchieved = 60.0 * 1000.0 * $distance / $duration;
        $percentageEstimateByHR = $this->getExpectedPercentageOfVO2maxAt($heartRateInPercent);
        $speedEstimateAt100Percent = $speedReallyAchieved / $percentageEstimateByHR;

        return $this->EstimationFormula->estimateFromVelocity($speedEstimateAt100Percent);
    }

    /**
     * @return float [ml/kg/min]
     */
    protected function estimateVO2maxByHeartRateWithElevation()
    {
        $elevationUp = $this->Activity->getElevation();
        $elevationDown = $elevationUp;

        if ($this->Activity->hasRoute()) {
            if ($this->Activity->getRoute()->getElevationUp() > 0 || $this->Activity->getRoute()->getElevationDown() > 0) {
                $elevationUp = $this->Activity->getRoute()->getElevationUp();
                $elevationDown = $this->Activity->getRoute()->getElevationDown();
            } elseif ($this->Activity->getRoute()->getElevation() > 0) {
                $elevationUp = $this->Activity->getRoute()->getElevation();
                $elevationDown = $elevationUp;
            }
        }

        return $this->estimateVO2maxByHeartRateWithElevationFor($elevationUp, $elevationDown);
    }

    /**
     * @param int $up [m]
     * @param int $down [m]
     * @return float [ml/kg/min]
     */
    protected function estimateVO2maxByHeartRateWithElevationFor($up, $down)
    {
        $modifier = new DistanceModifier($this->Activity->getDistance(), $up, $down);
        $modifier->setCorrectionValues($this->CorrectionForPositiveElevation, $this->CorrectionForNegativeElevation);

        return $this->estimateVO2maxByHeartRate($modifier->correctedDistance());
    }

    /**
     * Expected % of VO2max at given heart rate
     *
     * This formula is derived via regression from respective tables.
     *
     * @param float $heartRateInPercent in [0.0, 1.0]
     *
     * @return float in [0.0, 1.0]
     */
    protected function getExpectedPercentageOfVO2maxAt($heartRateInPercent)
    {
        return exp(($heartRateInPercent - 1.00466) / 0.68725);
    }
}
