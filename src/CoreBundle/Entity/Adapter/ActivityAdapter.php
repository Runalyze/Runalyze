<?php

namespace Runalyze\Bundle\CoreBundle\Entity\Adapter;

use League\Geotools\Geohash\Geohash;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\ClimbScoreCalculator;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\FlatOrHillyAnalyzer;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\NightDetector;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\PowerCalculator;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\TrimpCalculator;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\VO2maxCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Services\Import\TimezoneLookup;
use Runalyze\Profile\Weather\WeatherConditionProfile;
use Runalyze\Service\ElevationCorrection\StepwiseElevationProfileFixer;
use Runalyze\Util\LocalTime;

class ActivityAdapter
{
    /** @var Training */
    protected $Activity;

    public function __construct(Training $activity)
    {
        $this->Activity = $activity;
    }

    public function setAccountToRelatedEntities()
    {
        if ($this->Activity->hasRoute()) {
            $this->Activity->getRoute()->setAccount($this->Activity->getAccount());
        }

        if ($this->Activity->hasTrackdata()) {
            $this->Activity->getTrackdata()->setAccount($this->Activity->getAccount());
        }

        if ($this->Activity->hasSwimdata()) {
            $this->Activity->getSwimdata()->setAccount($this->Activity->getAccount());
        }

        if ($this->Activity->hasHrv()) {
            $this->Activity->getHrv()->setAccount($this->Activity->getAccount());
        }

        if ($this->Activity->hasRaceresult()) {
            $this->Activity->getRaceresult()->setAccount($this->Activity->getAccount());
        }
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

    /**
     * @return int [bpm]
     */
    public function getAverageHeartRateWithFallbackToTypeOrSport()
    {
        if ($this->Activity->getPulseAvg() > 0) {
            return $this->Activity->getPulseAvg();
        }

        if (null !== $this->Activity->getType()) {
            return $this->Activity->getType()->getHrAvg();
        }

        return null !== $this->Activity->getSport() ? $this->Activity->getSport()->getHfavg() : 100;
    }

    public function setActivityIdIfEmpty()
    {
        if (null === $this->Activity->getActivityId()) {
            $this->Activity->setActivityId((int)floor($this->Activity->getTime() / 60.0) * 60);
        }
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

    public function calculateEnergyConsumptionIfEmpty()
    {
        if (null === $this->Activity->getKcal() || 0 == $this->Activity->getKcal()) {
            $energyConsumption = $this->Activity->getSport()->getKcal() * $this->Activity->getS() / 3600.0;

            $this->Activity->setKcal($energyConsumption > 0 ? $energyConsumption : null);
        }
    }

    /**
     * @param int $gender enum, see \Runalyze\Profile\Athlete\Gender
     * @param int $heartRateMaximum [bpm]
     * @param int $heartRateResting [bpm]
     */
    public function calculateTrimp($gender, $heartRateMaximum, $heartRateResting)
    {
        $calculator = new TrimpCalculator();
        $calculator->calculateFor($this->Activity, $gender, $heartRateMaximum, $heartRateResting);
    }

    /**
     * @param int $heartRateMaximum [bpm]
     * @param int $correctionForPositiveElevation [m]
     * @param int $correctionForNegativeElevation [m]
     */
    public function calculateEffectiveVO2max($heartRateMaximum, $correctionForPositiveElevation, $correctionForNegativeElevation)
    {
        $calculator = new VO2maxCalculator();
        $calculator->calculateFor($this->Activity, $heartRateMaximum, $correctionForPositiveElevation, $correctionForNegativeElevation);
    }

    public function removeEffectiveVO2max()
    {
        $this->Activity->setVO2maxByTime(null);
        $this->Activity->setVO2max(null);
        $this->Activity->setVO2maxWithElevation(null);
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
        } else {
            $this->Activity->setNight(null);
        }
    }

    public function calculateValuesForSwimming()
    {
        $this->calculateTotalNumberOfStrokes();
        $this->calculateSwolf();
    }

    public function calculateTotalNumberOfStrokes()
    {
        if (null !== $this->Activity->getSwimdata() && $this->Activity->getSwimdata()->hasStrokes()) {
            $this->Activity->setTotalStrokes(array_sum($this->Activity->getSwimdata()->getStroke()));
        } else {
            $this->Activity->setTotalStrokes(null);
        }
    }

    public function calculateSwolf()
    {
        if (null !== $this->Activity->getTotalStrokes() && null !== $this->Activity->getTrackdata() && $this->Activity->getTrackdata()->hasTime()) {
            $trackDataTime = $this->Activity->getTrackdata()->getTime();
            $totalTime = end($trackDataTime);
            $numberOfPoints = count($trackDataTime);

            $this->Activity->setSwolf((int)round(($this->Activity->getTotalStrokes() + $totalTime) / $numberOfPoints));
        } else {
            $this->Activity->setSwolf(null);
        }
    }

    public function useElevationFromRoute()
    {
        if ($this->Activity->hasRoute()) {
            $this->Activity->setElevation($this->Activity->getRoute()->getElevation());
        } else {
            $this->Activity->setElevation(null);
        }
    }

    public function calculateClimbScore()
    {
        if ($this->canCalculateClimbScore()) {
            $elevationsBackup = $this->temporarilyFixStepwiseElevationsInRoute();

            (new FlatOrHillyAnalyzer())->calculatePercentageHillyFor($this->Activity);
            (new ClimbScoreCalculator())->calculateFor($this->Activity);

            $this->revertTemporaryFixForStepwiseElevationsInRoute($elevationsBackup);
        } else {
            $this->Activity->setPercentageHilly(null);
            $this->Activity->setClimbScore(null);
        }
    }

    /**
     * @return bool
     */
    protected function canCalculateClimbScore()
    {
        return (
            null !== $this->Activity->getRoute() &&
            $this->Activity->getRoute()->hasElevations() &&
            null !== $this->Activity->getTrackdata() &&
            $this->Activity->getTrackdata()->hasDistance()
        );
    }

    protected function temporarilyFixStepwiseElevationsInRoute()
    {
        $backupElevations = $this->Activity->getRoute()->getElevationsCorrected();

        if (null !== $backupElevations) {
            $this->Activity->getRoute()->setElevationsCorrected(
                (new StepwiseElevationProfileFixer(5, StepwiseElevationProfileFixer::METHOD_VARIABLE_GROUP_SIZE))
                    ->fixStepwiseElevations(
                        $this->Activity->getRoute()->getElevationsCorrected(),
                        $this->Activity->getTrackdata()->getDistance()
                    )
            );
        }

        return $backupElevations;
    }

    /**
     * @param array|null $backupElevations [m]
     */
    protected function revertTemporaryFixForStepwiseElevationsInRoute(array $backupElevations = null)
    {
        $this->Activity->getRoute()->setElevationsCorrected($backupElevations);
    }

    public function guessTimezoneBasedOnCoordinates(TimezoneLookup $timezoneLookup)
    {
        if (
            null === $this->Activity->getTimezoneOffset() &&
            $this->Activity->hasRoute() &&
            $this->Activity->getRoute()->hasGeohashes() &&
            $timezoneLookup->isPossible()
        ) {
            $this->Activity->getRoute()->setStartEndGeohashes();

            $startPoint = $this->Activity->getRoute()->getStartpoint();

            if (null !== $startPoint) {
                $coordinate = (new Geohash())->decode($startPoint)->getCoordinate();
                $timezone = $timezoneLookup->getTimezoneForCoordinate($coordinate->getLongitude(), $coordinate->getLatitude());
                $this->updateTimezoneForActivity($timezone);
            }
        }
    }

    /**
     * @param string|null $timezone
     */
    protected function updateTimezoneForActivity($timezone)
    {
        if (null === $timezone) {
            return;
        }

        $newOffset = (new \DateTime(null, new \DateTimeZone($timezone)))->setTimestamp($this->Activity->getTime())->getOffset() / 60;

        if (null !== $this->Activity->getTimezoneOffset()) {
            $this->Activity->setTime($this->Activity->getTime() + 60 * ($newOffset - $this->Activity->getTimezoneOffset()));
        }

        $this->Activity->setTimezoneOffset($newOffset);
    }
}
