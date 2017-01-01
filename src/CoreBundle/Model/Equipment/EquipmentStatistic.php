<?php

namespace Runalyze\Bundle\CoreBundle\Model\Equipment;

use Runalyze\Bundle\CoreBundle\Entity\Equipment;

class EquipmentStatistic
{
    /** @var Equipment */
    protected $Equipment;

    /** @var null|int */
    protected $NumberOfActivities = null;

    /** @var null|float [s/km] */
    protected $MaximalPace = null;

    /** @var null|float [km] */
    protected $MaximalDistance = null;

    public function __construct(Equipment $equipment)
    {
        $this->Equipment = $equipment;
    }

    /**
     * @return Equipment
     */
    public function getEquipment()
    {
        return $this->Equipment;
    }

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
     * @param float|null $secondsPerKilometer [s/km]
     */
    public function setMaximalPace($secondsPerKilometer)
    {
        $this->MaximalPace = $secondsPerKilometer;
    }

    /**
     * @return float|null [s/km]
     */
    public function getMaximalPace()
    {
        return $this->MaximalPace;
    }

    /**
     * @param float|null $totalDistance [km]
     */
    public function setMaximalDistance($totalDistance)
    {
        $this->MaximalDistance = $totalDistance;
    }

    /**
     * @return float|null [km]
     */
    public function getMaximalDistance()
    {
        return $this->MaximalDistance;
    }
}
