<?php

namespace Runalyze\Sports\EnergyExpenditure;

use Runalyze\Athlete;
use Runalyze\Metrics\Energy\Energy;
use Runalyze\Metrics\Energy\Unit\Kilojoules;

/**
 * @see Keytal, L. R., et al.: Prediction of energy expenditure from heart rate monitoring during submaximal exercise,
 *      Journal of Sports Sciences, 2005, http://www.braydenwm.com/cal_vs_hr_ref_paper.pdf, p. 11
 */
class HeartRateBasedEstimator
{
    /** @var Athlete */
    protected $Athlete;

    /** @var float [kg] */
    const DEFAULT_WEIGHT_MALE = 75.0;

    /** @var float [kg] */
    const DEFAULT_WEIGHT_FEMALE = 65.0;

    /** @var int */
    const DEFAULT_AGE = 35;

    /** @var Energy */
    protected $Energy;

    public function __construct(Athlete $athlete)
    {
        $this->Athlete = $athlete;
        $this->Energy = new Energy();
    }

    /**
     * @param int $heartRateInBpm
     * @return Energy
     */
    public function getExpenditurePerMinute($heartRateInBpm)
    {
        if ($this->Athlete->knowsGender() && $this->Athlete->isFemale()) {
            $kiloJoulesPerMinute = $this->getExpenditureForFemale($heartRateInBpm);
        } else {
            $kiloJoulesPerMinute = $this->getExpenditureForMale($heartRateInBpm);
        }

        return $this->Energy->setValue($kiloJoulesPerMinute, new Kilojoules());
    }

    /**
     * @param int $heartRateInBpm [bpm]
     * @return float [kcal]
     */
    protected function getExpenditureForFemale($heartRateInBpm)
    {
        return -20.4022 + 0.4472 * $heartRateInBpm - 0.1263 * $this->getWeight(self::DEFAULT_WEIGHT_FEMALE) + 0.074 * $this->getAge();
    }

    /**
     * @param int $heartRateInBpm [bpm]
     * @return float [kcal]
     */
    protected function getExpenditureForMale($heartRateInBpm)
    {
        return -55.0969 + 0.6309 * $heartRateInBpm - 0.1988 * $this->getWeight(self::DEFAULT_WEIGHT_MALE) + 0.2017 * $this->getAge();
    }

    /**
     * @param float|int $defaultWeight [kg]
     * @return float|int
     */
    protected function getWeight($defaultWeight)
    {
        return $this->Athlete->knowsWeight() ? $this->Athlete->weight() : $defaultWeight;
    }

    /**
     * @return int [age in years]
     */
    protected function getAge()
    {
        return $this->Athlete->knowsAge() ? $this->Athlete->age() : self::DEFAULT_AGE;
    }
}
