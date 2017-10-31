<?php

namespace Runalyze\Bundle\CoreBundle\Entity\Adapter;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\NightDetector;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\PowerCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Profile\Weather\WeatherConditionProfile;
use Runalyze\Util\LocalTime;

class ActivityAdapter
{
    /** @var Training */
    protected $Activity;

    public function __construct(Training $activity)
    {
        $this->Activity = $activity;
    }

    /**
     * @return int
     */
    public function getAgeOfActivity()
    {
        return LocalTime::now() - $this->Activity->getTime();
    }

    /**
     * @param int $days
     * @return bool
     */
    public function isNotOlderThanXDays($days)
    {
        return  (new LocalTime())->diff(new LocalTime($this->Activity->getTime()))->days <= $days;
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return null !== $this->Activity->getSport() && $this->Activity->getSport()->getInternalSport()->isRunning();
    }

    /**
     * @return bool
     */
    public function isCycling()
    {
        return null !== $this->Activity->getSport() && $this->Activity->getSport()->getInternalSport()->isCycling();
    }

    /**
     * @return bool
     */
    public function isSwimming()
    {
        return null !== $this->Activity->getSport() && $this->Activity->getSport()->getInternalSport()->isSwimming();
    }

    public function updateSimpleCalculatedValues()
    {
        $this->calculateStrideLength();
        $this->calculateVerticalRatio();
    }

    protected function calculateStrideLength()
    {
        $strideLength = null;

        if ($this->isRunning() && $this->Activity->getS() > 0 && $this->Activity->getCadence() > 0 && $this->Activity->getDistance() > 0) {
            $strideLength = (int)round($this->Activity->getDistance() * 1000.0 * 100.0 / ($this->Activity->getCadence() * 2.0 / 60.0 * $this->Activity->getS()));

            if ($strideLength > 255) {
                $strideLength = null;
            }
        }

        $this->Activity->setStrideLength($strideLength);
    }

    protected function calculateVerticalRatio()
    {
        $verticalRatio = null;

        if ($this->Activity->getVerticalOscillation() > 0 && $this->Activity->getStrideLength() > 0) {
            $verticalRatio = (int)round(100.0 * $this->Activity->getVerticalOscillation() / $this->Activity->getStrideLength());
        }

        $this->Activity->setVerticalRatio($verticalRatio);
    }

    /**
     * @param int $numberOfDaysToConsiderForShape
     * @return bool
     */
    public function isRelevantForCurrentMarathonShape($numberOfDaysToConsiderForShape)
    {
        return (
            $this->isRunning() &&
            $this->isNotOlderThanXDays($numberOfDaysToConsiderForShape)
        );
    }

    /**
     * @param int $numberOfDaysToConsiderForShape
     * @return bool
     */
    public function isRelevantForCurrentEffectiveVO2maxShape($numberOfDaysToConsiderForShape)
    {
        return (
            $this->isRunning() &&
            $this->Activity->getUseVO2max() &&
            $this->Activity->getVO2max() > 0.0 &&
            $this->isNotOlderThanXDays($numberOfDaysToConsiderForShape)
        );
    }

    /**
     * @param float|int|null $athleteWeight [kg]
     * @param float|int|null $bikeWeight [kg]
     */
    public function calculatePower($athleteWeight = null, $bikeWeight = null)
    {
        $calculator = new PowerCalculator();
        $calculator->calculateFor($this->Activity);
    }

    public function removePower()
    {
        $this->Activity->setPowerCalculated(null);
        $this->Activity->setPower(null);

        if ($this->Activity->hasTrackdata()) {
            $this->Activity->getTrackdata()->setPower(null);
        }
    }

    public function removeWeather()
    {
        $this->Activity->setWeatherid(WeatherConditionProfile::UNKNOWN);
        $this->Activity->setWeatherSource(null);
        $this->Activity->setTemperature(null);
        $this->Activity->setWindSpeed(null);
        $this->Activity->setWindDeg(null);
        $this->Activity->setPressure(null);
        $this->Activity->setHumidity(null);
    }

    public function calculateIfActivityWasAtNight()
    {
        if (null !== $this->Activity->getRoute() && $this->Activity->getRoute()->hasGeohashes()) {
            $this->Activity->setNight((new NightDetector())->isActivityAtNight($this->Activity));
        }
    }
}
