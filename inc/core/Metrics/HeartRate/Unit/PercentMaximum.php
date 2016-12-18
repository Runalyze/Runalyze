<?php

namespace Runalyze\Metrics\HeartRate\Unit;

class PercentMaximum extends AbstractHeartRateUnitInPercent
{
    /** @var int */
    protected $MaximalHeartRate;

    /**
     * @param int $maximalHeartRate
     */
    public function __construct($maximalHeartRate)
    {
        $this->MaximalHeartRate = $maximalHeartRate;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return '%HRmax';
    }

    /**
     * @param mixed $valueInThisUnit
     * @return mixed
     */
    public function toBaseUnit($valueInThisUnit)
    {
        return $valueInThisUnit * $this->MaximalHeartRate;
    }

    /**
     * @param mixed $valueInBaseUnit
     * @return mixed
     */
    public function fromBaseUnit($valueInBaseUnit)
    {
        return $valueInBaseUnit / $this->MaximalHeartRate;
    }

    /**
     * @return int
     */
    public function getMaximalHeartRate()
    {
        return $this->MaximalHeartRate;
    }
}
