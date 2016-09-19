<?php

namespace Runalyze\Bundle\CoreBundle\Component\Activity\Tool;

use Runalyze\Calculation\Distribution\MultipleTimeSeries;
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
     * @throws \InvalidArgumentException
     */
    public function __construct(Trackdata\Entity $trackdata)
    {
        if (!$trackdata->has(Trackdata\Entity::TIME)) {
            throw new \InvalidArgumentException('Provided trackdata object must have time array.');
        }

        $this->Trackdata = $trackdata;

        $this->determineAvailableKeys();
        $this->calculateStatistics();
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

    protected function calculateStatistics()
    {
        $dataOfAvailableSeries = [];

        foreach (array_keys($this->AvailableKeys) as $key) {
            $dataOfAvailableSeries[$key] = $this->Trackdata->get($key);
        }

        $this->MultipleTimeSeries = new MultipleTimeSeries($dataOfAvailableSeries, $this->Trackdata->get(Trackdata\Entity::TIME));
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
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
     * @return \Runalyze\Calculation\Distribution\Empirical
     */
    public function getStatisticsForTotalHemoglobin2()
    {
        return $this->MultipleTimeSeries->getDistribution(Trackdata\Entity::THB_1);
    }
}
