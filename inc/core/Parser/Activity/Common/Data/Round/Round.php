<?php

namespace Runalyze\Parser\Activity\Common\Data\Round;

class Round
{
    /** @var float [km] */
    protected $Distance;

    /** @var int [s] */
    protected $Duration;

    /** @var bool */
    protected $IsActive = true;

    /**
     * @param float $distance [km]
     * @param int $duration [s]
     * @param bool $isActive
     */
    public function __construct($distance, $duration, $isActive = true)
    {
        $this->Distance = $distance;
        $this->Duration = $duration;
        $this->IsActive = (bool)$isActive;
    }

    /**
     * @param float $distance [km]
     */
    public function setDistance($distance)
    {
        $this->Distance = $distance;
    }

    /**
     * @return float [km]
     */
    public function getDistance()
    {
        return $this->Distance;
    }

    /**
     * @param int $duration [s]
     */
    public function setDuration($duration)
    {
        $this->Duration = $duration;
    }

    /**
     * @return int [s]
     */
    public function getDuration()
    {
        return $this->Duration;
    }

    /**
     * @param bool $flag
     */
    public function setActive($flag = true)
    {
        $this->IsActive = (bool)$flag;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->IsActive;
    }
}
