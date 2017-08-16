<?php

namespace Runalyze\Bundle\CoreBundle\Model\Sport;

use Runalyze\Bundle\CoreBundle\Entity\Sport;

class SportStatistic
{
    /** @var Sport */
    protected $Sport;

    /** @var null|int */
    protected $NumberOfActivities = null;

    /** @var null|int */
    protected $NumberOfActivitiesWithDistance = null;

    /** @var null|float */
    protected $TotalDistance = null;

    /** @var null|float */
    protected $TotalDuration = null;

    public function __construct(Sport $sport)
    {
        $this->Sport = $sport;
    }

    /**
     * @return Sport
     */
    public function getSport()
    {
        return $this->Sport;
    }

    /**
     * @param int $numberOfActivities
     * @param int|null $numberOfActivitiesWithDistance
     */
    public function setNumberOfActivities($numberOfActivities, $numberOfActivitiesWithDistance = null)
    {
        $this->NumberOfActivities = (int)$numberOfActivities;
        $this->NumberOfActivitiesWithDistance = null !== $numberOfActivitiesWithDistance ? (int)$numberOfActivitiesWithDistance : null;
    }

    /**
     * @return int|null
     */
    public function getNumberOfActivities()
    {
        return $this->NumberOfActivities;
    }

    /**
     * @return bool
     */
    public function areMostActivitiesWithDistance()
    {
        if (null !== $this->NumberOfActivitiesWithDistance) {
            return $this->NumberOfActivitiesWithDistance >= $this->NumberOfActivities / 2.0;
        }

        return null !== $this->TotalDistance && $this->TotalDistance > 0.0;
    }

    /**
     * @param float $kilometer [km]
     */
    public function setTotalDistance($kilometer)
    {
        $this->TotalDistance = null !== $kilometer ? (float)$kilometer : null;
    }

    /**
     * @return float|null [km]
     */
    public function getTotalDistance()
    {
        return $this->TotalDistance;
    }

    /**
     * @return bool
     */
    public function isTotalDistanceKnown()
    {
        return null !== $this->TotalDistance;
    }

    /**
     * @param float $timeInSeconds [s]
     */
    public function setTotalDuration($timeInSeconds)
    {
        $this->TotalDuration = null !== $timeInSeconds ? (float)$timeInSeconds : null;
    }

    /**
     * @return float|null [s]
     */
    public function getTotalDuration()
    {
        return $this->TotalDuration;
    }

    /**
     * @return bool
     */
    public function isTotalDurationKnown()
    {
        return null !== $this->TotalDuration;
    }
}
