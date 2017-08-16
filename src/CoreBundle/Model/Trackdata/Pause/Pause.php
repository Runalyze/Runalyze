<?php

namespace Runalyze\Bundle\CoreBundle\Model\Trackdata\Pause;

class Pause
{
    /** @var int [s] */
    protected $TimeIndex;

    /** @var int [s] */
    protected $Duration;

    /** @var int|null [bpm] */
    protected $HeartRateAtStart = null;

    /** @var int|null [bpm] */
    protected $HeartRateAtEnd = null;

    /** @var int|null [bpm] */
    protected $HeartRateAtRecovery = null;

    /** @var int|null [s] */
    protected $TimeUntilRecovery = null;

    /** @var int|null relative to current baseline */
    protected $PerformanceCondition = null;

    /**
     * @param int $timeIndex
     * @param int $duration
     */
    public function __construct($timeIndex, $duration)
    {
        $this->TimeIndex = $timeIndex;
        $this->Duration = $duration;
    }

    /**
     * @return int [s]
     */
    public function getTimeIndex()
    {
        return $this->TimeIndex;
    }

    /**
     * @return int [s]
     */
    public function getDuration()
    {
        return $this->Duration;
    }

    /**
     * @param int|null $atStart [bpm]
     * @param int|null $atEnd [bpm]
     * @return $this
     */
    public function setHeartRateDetails($atStart, $atEnd)
    {
        $this->HeartRateAtStart = $atStart;
        $this->HeartRateAtEnd = $atEnd;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasHeartRateDetails()
    {
        return null !== $this->HeartRateAtStart && null !== $this->HeartRateAtEnd;
    }

    /**
     * @return int|null [bpm]
     */
    public function getHeartRateAtStart()
    {
        return $this->HeartRateAtStart;
    }

    /**
     * @return int|null [bpm]
     */
    public function getHeartRateAtEnd()
    {
        return $this->HeartRateAtEnd;
    }

    /**
     * @return int|null [bpm]
     */
    public function getHeartRateDifference()
    {
        return $this->HeartRateAtStart - $this->HeartRateAtEnd;
    }

    /**
     * @return bool
     */
    public function hasRecoveryDetails()
    {
        return null !== $this->HeartRateAtRecovery && null !== $this->TimeUntilRecovery;
    }

    /**
     * @param int|null $heartRate [bpm]
     * @param int|null $timeUntilRecovery [s]
     * @return $this
     */
    public function setRecoveryDetails($heartRate, $timeUntilRecovery)
    {
        $this->HeartRateAtRecovery = $heartRate;
        $this->TimeUntilRecovery = $timeUntilRecovery;

        return $this;
    }

    /**
     * @return int|null [bpm]
     */
    public function getHeartRateAtRecovery()
    {
        return $this->HeartRateAtRecovery;
    }

    /**
     * @return int|null [s]
     */
    public function getTimeUntilRecovery()
    {
        return $this->TimeUntilRecovery;
    }

    /**
     * @param int|null $performanceCondition relative to current baseline
     * @return $this
     */
    public function setPerformanceCondition($performanceCondition)
    {
        $this->PerformanceCondition = $performanceCondition;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPerformanceCondition()
    {
        return null !== $this->PerformanceCondition;
    }

    /**
     * @return int|null relative to current baseline
     */
    public function getPerformanceCondition()
    {
        return $this->PerformanceCondition;
    }
}
