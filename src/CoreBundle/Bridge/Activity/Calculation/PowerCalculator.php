<?php

namespace Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Calculation\Power\Calculator;
use Runalyze\Model\Route;
use Runalyze\Model\Trackdata;
use Runalyze\Profile\Sport\AbstractSport;

class PowerCalculator
{
    /** @var float [kg] */
    protected $AthleteWeight = 75.0;

    /** @var float [kg] */
    protected $BikeWeight = 0.0;

    /** @var Training */
    protected $Activity;

    /**
     * @param Training $activity
     * @param float $athleteWeight [kg]
     * @param float $bikeWeight [kg]
     */
    public function calculateFor(Training $activity, $athleteWeight = 75.0, $bikeWeight = 0.0)
    {
        $this->Activity = $activity;
        $this->AthleteWeight = $athleteWeight;
        $this->BikeWeight = $bikeWeight;

        if ($this->hasActivityPowerDataFromDevice()) {
            return;
        }

        if ($this->canCalculatePower()) {
            list($powerData, $avgPower) = $this->calculatePower();

            $this->setPowerValues($this->Activity, $powerData, $avgPower);
        } elseif (true === $this->Activity->isPowerCalculated()) {
            $this->setPowerValuesToNull($this->Activity);
        }
    }

    /**
     * @return bool
     */
    protected function hasActivityPowerDataFromDevice()
    {
        return false === $this->Activity->isPowerCalculated();
    }

    /**
     * @return bool
     */
    protected function canCalculatePower()
    {
        return (
            null !== $this->Activity->getSport() &&
            $this->canCalculatePowerForSport($this->Activity->getSport()->getInternalSport()) &&
            $this->canCalculatePowerForActivity($this->Activity)
        );
    }

    /**
     * @param AbstractSport $sport
     * @return bool
     */
    protected function canCalculatePowerForSport(AbstractSport $sport)
    {
        return $sport->isCycling();
    }

    /**
     * @param Training $activity
     * @return bool
     */
    protected function canCalculatePowerForActivity(Training $activity)
    {
        return (
            $activity->hasRoute() &&
            $activity->getRoute()->hasElevations() &&
            $activity->hasTrackdata() &&
            $activity->getTrackdata()->hasTime() &&
            $activity->getTrackdata()->hasDistance()
        );
    }

    protected function setPowerValuesToNull(Training $activity)
    {
        $activity->setPower(null);
        $activity->setPowerCalculated(null);
    }

    /**
     * @param Training $activity
     * @param array $power [W]
     * @param int $avgPower [W]
     */
    protected function setPowerValues(Training $activity, array $power, $avgPower)
    {
        $activity->getTrackdata()->setPower($power);

        $activity->setPower($avgPower);
        $activity->setPowerCalculated(true);
    }

    /**
     * @return array [[power_1, ...], avgPower] [W]
     */
    protected function calculatePower()
    {
        $calculator = new Calculator(
            new Trackdata\Entity([
                Trackdata\Entity::TIME => $this->Activity->getTrackdata()->getTime(),
                Trackdata\Entity::DISTANCE => $this->Activity->getTrackdata()->getDistance()
            ]),
            new Route\Entity([
                Route\Entity::ELEVATIONS_CORRECTED => $this->Activity->getRoute()->getElevations()
            ])
        );

        $calculator->calculate($this->AthleteWeight + $this->BikeWeight);

        return [
            $calculator->powerData(),
            $calculator->average()
        ];
    }
}
