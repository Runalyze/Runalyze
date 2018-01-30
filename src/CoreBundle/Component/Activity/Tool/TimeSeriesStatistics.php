<?php

namespace Runalyze\Bundle\CoreBundle\Component\Activity\Tool;

use Runalyze\Mathematics\Distribution\MultipleTimeSeries;
use Runalyze\Model\Trackdata;

class TimeSeriesStatistics
{
    /** @var Trackdata\Entity */
    protected $Trackdata;

    /** @var array */
    protected $AvailableKeys = [];

    /** @var MultipleTimeSeries|null */
    protected $MultipleTimeSeries = null;

    /**
     * @param Trackdata\Entity $trackdata
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Trackdata\Entity $trackdata)
    {
        if (!$trackdata->has(Trackdata\Entity::TIME)) {
            throw new \InvalidArgumentException('Provided trackdata object must have time array.');
        }

        $this->Trackdata = $trackdata;

        $this->determineAvailableKeys();
        $this->manipulateNonIntegerSeries();
    }

    protected function determineAvailableKeys()
    {
        $potentialKeys = [
            Trackdata\Entity::PACE,
            Trackdata\Entity::HEARTRATE,
            Trackdata\Entity::CADENCE,
            Trackdata\Entity::STRIDE_LENGTH,
            Trackdata\Entity::POWER,
            Trackdata\Entity::GROUNDCONTACT,
            Trackdata\Entity::GROUNDCONTACT_BALANCE,
            Trackdata\Entity::VERTICAL_OSCILLATION,
            Trackdata\Entity::VERTICAL_RATIO,
            Trackdata\Entity::IMPACT_GS_LEFT,
            Trackdata\Entity::IMPACT_GS_RIGHT,
            Trackdata\Entity::BRAKING_GS_LEFT,
            Trackdata\Entity::BRAKING_GS_RIGHT,
            Trackdata\Entity::FOOTSTRIKE_TYPE_LEFT,
            Trackdata\Entity::FOOTSTRIKE_TYPE_RIGHT,
            Trackdata\Entity::PRONATION_EXCURSION_LEFT,
            Trackdata\Entity::PRONATION_EXCURSION_RIGHT,
            Trackdata\Entity::SMO2_0,
            Trackdata\Entity::SMO2_1,
            Trackdata\Entity::THB_0,
            Trackdata\Entity::THB_1
        ];

        foreach ($potentialKeys as $key) {
            if ($this->Trackdata->has($key)) {
                $this->AvailableKeys[$key] = true;
            }
        }
    }

    protected function manipulateNonIntegerSeries()
    {
        $nonIntegerKeys = [
            Trackdata\Entity::IMPACT_GS_LEFT => 10,
            Trackdata\Entity::IMPACT_GS_RIGHT => 10,
            Trackdata\Entity::BRAKING_GS_LEFT => 10,
            Trackdata\Entity::BRAKING_GS_RIGHT => 10,
            Trackdata\Entity::PRONATION_EXCURSION_LEFT => 10,
            Trackdata\Entity::PRONATION_EXCURSION_RIGHT => 10
        ];
        $hasNonIntegerKeys = false;

        foreach (array_keys($nonIntegerKeys) as $key) {
            if (isset($this->AvailableKeys[$key])) {
                $hasNonIntegerKeys = true;
                break;
            }
        }

        if ($hasNonIntegerKeys) {
            $this->Trackdata = clone $this->Trackdata;

            foreach ($nonIntegerKeys as $key => $factor) {
                $this->Trackdata->set($key, array_map(function($v) use ($factor) {
                    return (int)round($v * $factor);
                }, $this->Trackdata->get($key)));
            }
        }
    }

    /**
     * @param float[] $quantiles
     */
    public function calculateStatistics(array $quantiles = [])
    {
        $dataOfAvailableSeries = [];

        foreach (array_keys($this->AvailableKeys) as $key) {
            $dataOfAvailableSeries[$key] = $this->Trackdata->get($key);
        }

        $this->MultipleTimeSeries = new MultipleTimeSeries();
        $this->MultipleTimeSeries->setQuantiles($quantiles);
        $this->MultipleTimeSeries->generateDistributionsFor($dataOfAvailableSeries, $this->Trackdata->get(Trackdata\Entity::TIME));
    }

    /**
     * @param mixed $key enum from trackdata model class
     * @return bool
     */
    public function hasStatisticsFor($key)
    {
        return isset($this->AvailableKeys[$key]);
    }

    /**
     * @param $key
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsFor($key)
    {
        return $this->MultipleTimeSeries->getDistribution($key);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForPace()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::PACE);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForPace()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::PACE);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForHeartRate()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::HEARTRATE);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForHeartRate()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::HEARTRATE);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForCadence()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::CADENCE);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForCadence()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::CADENCE);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForStrideLength()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::STRIDE_LENGTH);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForStrideLength()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::STRIDE_LENGTH);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForPower()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::POWER);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForPower()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::POWER);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForGroundcontact()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::GROUNDCONTACT);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForGroundcontact()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::GROUNDCONTACT);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForGroundcontactBalance()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::GROUNDCONTACT_BALANCE);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForGroundcontactBalance()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::GROUNDCONTACT_BALANCE);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForVerticalOscillation()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::VERTICAL_OSCILLATION);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForVerticalOscillation()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::VERTICAL_OSCILLATION);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForVerticalRatio()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::VERTICAL_RATIO);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForVerticalRatio()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::VERTICAL_RATIO);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForSaturatedHemoglobin()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::SMO2_0);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForSaturatedHemoglobin()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::SMO2_0);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForSaturatedHemoglobin2()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::SMO2_1);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForSaturatedHemoglobin2()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::SMO2_1);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForTotalHemoglobin()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::THB_0);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForTotalHemoglobin()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::THB_0);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForTotalHemoglobin2()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::THB_1);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForTotalHemoglobin2()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::THB_1);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForImpactGsLeft()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::IMPACT_GS_LEFT);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForImpactGsLeft()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::IMPACT_GS_LEFT);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForImpactGsRight()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::IMPACT_GS_RIGHT);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForImpactGsRight()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::IMPACT_GS_RIGHT);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForBrakingGsLeft()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::BRAKING_GS_LEFT);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForBrakingGsLeft()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::BRAKING_GS_LEFT);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForBrakingGsRight()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::BRAKING_GS_RIGHT);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForBrakingGsRight()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::BRAKING_GS_RIGHT);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForFootstrikeTypeLeft()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::FOOTSTRIKE_TYPE_LEFT);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForFootstrikeTypeLeft()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::FOOTSTRIKE_TYPE_RIGHT);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForFootstrikeTypeRight()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::FOOTSTRIKE_TYPE_RIGHT);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForFootstrikeTypeRight()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::FOOTSTRIKE_TYPE_LEFT);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForPronationExcursionLeft()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::PRONATION_EXCURSION_LEFT);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForPronationExcursionLeft()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::PRONATION_EXCURSION_LEFT);
    }

    /**
     * @return bool
     */
    public function hasStatisticsForPronationExcursionRight()
    {
        return $this->hasStatisticsFor(Trackdata\Entity::PRONATION_EXCURSION_RIGHT);
    }

    /**
     * @return \Runalyze\Mathematics\Distribution\EmpiricalDistribution
     */
    public function getStatisticsForPronationExcursionRight()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::PRONATION_EXCURSION_RIGHT);
    }
}
