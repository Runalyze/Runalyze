<?php

namespace Runalyze\Metrics\HeartRate\Unit;

class PercentReserve extends AbstractHeartRateUnitInPercent
{
    /** @var int */
    protected $MaximalHeartRate;

    /** @var int */
    protected $RestingHeartRate;

    /**
     * @param int $maximalHeartRate
     * @param int $restingHeartRate
     */
    public function __construct($maximalHeartRate, $restingHeartRate)
    {
        $this->MaximalHeartRate = $maximalHeartRate;
        $this->RestingHeartRate = $restingHeartRate;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return '%HRreserve';
    }

    /**
     * @param mixed $valueInThisUnit
     * @return mixed
     */
    public function toBaseUnit($valueInThisUnit)
    {
        return $this->RestingHeartRate + $valueInThisUnit * ($this->MaximalHeartRate - $this->RestingHeartRate);
    }

    /**
     * @param mixed $valueInBaseUnit
     * @return mixed
     */
    public function fromBaseUnit($valueInBaseUnit)
    {
        return ($valueInBaseUnit - $this->RestingHeartRate) / ($this->MaximalHeartRate - $this->RestingHeartRate);
    }

    /**
     * @return int
     */
    public function getMaximalHeartRate()
    {
        return $this->MaximalHeartRate;
    }

    /**
     * @return int
     */
    public function getRestingHeartRate()
    {
        return $this->RestingHeartRate;
    }

    /**
     * @return string value in base unit is given as 'd'
     */
    public function getJavaScriptConversion()
    {
        return '100*(d - '.$this->RestingHeartRate.')/'.($this->MaximalHeartRate - $this->RestingHeartRate);
    }
}
