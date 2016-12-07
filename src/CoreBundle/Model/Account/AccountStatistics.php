<?php

namespace Runalyze\Bundle\CoreBundle\Model\Account;

class AccountStatistics
{
    /** @var null|int */
    protected $NumberOfActivities = null;

    /** @var null|float [s] */
    protected $TotalDuration = null;

    /** @var null|float [km] */
    protected $TotalDistance = null;

    /**
     * @param null|int $numberOfActivities
     */
    public function setNumberOfActivities($numberOfActivities)
    {
        $this->NumberOfActivities = $numberOfActivities;
    }

    /**
     * @return int|null
     */
    public function getNumberOfActivities()
    {
        return $this->NumberOfActivities;
    }

    /**
     * @param float|null $totalDuration [s]
     */
    public function setTotalDuration($totalDuration)
    {
        $this->TotalDuration = $totalDuration;
    }

    /**
     * @return float|null [s]
     */
    public function getTotalDuration()
    {
        return $this->TotalDuration;
    }

    /**
     * @param float|null $totalDistance [km]
     */
    public function setTotalDistance($totalDistance)
    {
        $this->TotalDistance = $totalDistance;
    }

    /**
     * @return float|null [km]
     */
    public function getTotalDistance()
    {
        return $this->TotalDistance;
    }
}
