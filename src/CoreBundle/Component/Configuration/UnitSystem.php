<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration;

use Runalyze\Activity\PaceUnit\KmPerHour;
use Runalyze\Activity\PaceUnit\MilesPerHour;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Metrics\Cadence;
use Runalyze\Metrics\Common\Unit\Factorial;
use Runalyze\Metrics\Distance;
use Runalyze\Metrics\HeartRate\Unit\PercentMaximum;
use Runalyze\Metrics\LegacyUnitConverter;
use Runalyze\Metrics\Velocity\Unit\AbstractPaceUnit;
use Runalyze\Metrics\Velocity\Unit\KilometerPerHour;
use Runalyze\Parameter\Application\DistanceUnitSystem;

class UnitSystem
{
    /** @var RunalyzeConfigurationList */
    protected $Configuration;

    /** @var AbstractPaceUnit */
    protected $PaceUnit;

    /** @var LegacyUnitConverter */
    protected $LegacyUnitConverter;

    public function __construct(RunalyzeConfigurationList $config)
    {
        $this->Configuration = $config;
        $this->PaceUnit = new KilometerPerHour();
        $this->LegacyUnitConverter = new LegacyUnitConverter();
    }

    public function setPaceUnitFromSport(Sport $sport)
    {
        $this->PaceUnit = $sport->getSpeedUnit();
    }

    public function setPaceUnit(AbstractPaceUnit $paceUnit)
    {
        $this->PaceUnit = $paceUnit;
    }

    /**
     * @return AbstractPaceUnit
     */
    public function getPaceUnit(Sport $sport = null)
    {
        if (null !== $sport) {
            return $sport->getSpeedUnit();
        }

        return $this->PaceUnit;
    }

    /**
     * @return Cadence\Unit\AbstractCadenceUnit
     */
    public function getCadenceUnit()
    {
        return new Cadence\Unit\RoundsPerMinute();
    }

    /**
     * @return Distance\Unit\AbstractDistanceUnit
     */
    public function getDistanceUnit()
    {
        if (DistanceUnitSystem::IMPERIAL === $this->Configuration->get('general.DISTANCE_UNIT_SYSTEM')) {
            return new Distance\Unit\Miles();
        }

        return new Distance\Unit\Kilometer();
    }

    /**
     * @return Distance\Unit\AbstractDistanceUnit
     */
    public function getElevationUnit()
    {
        if (DistanceUnitSystem::IMPERIAL === $this->Configuration->get('general.DISTANCE_UNIT_SYSTEM')) {
            return new Distance\Unit\Feet();
        }

        return new Distance\Unit\Meter();
    }

    /**
     * @return Distance\Unit\AbstractDistanceUnit
     */
    public function getStrideLengthUnit()
    {
        if (DistanceUnitSystem::IMPERIAL === $this->Configuration->get('general.DISTANCE_UNIT_SYSTEM')) {
            return new Distance\Unit\Feet();
        }

        return new Distance\Unit\Meter();
    }

    /**
     * @return \Runalyze\Metrics\Energy\Unit\AbstractEnergyUnit
     */
    public function getEnergyUnit()
    {
        return $this->LegacyUnitConverter->getEnergyUnit(
            $this->Configuration->get('general.ENERGY_UNIT')
        );
    }

    /**
     * @param int|null $maximalHeartRate [bpm]
     * @param int|null $restingHeartRate [bpm]
     * @return \Runalyze\Metrics\HeartRate\Unit\AbstractHeartRateUnit
     */
    public function getHeartRateUnit($maximalHeartRate = null, $restingHeartRate = null)
    {
        $maximalHeartRate = $maximalHeartRate ?: $this->Configuration->get('data.HF_MAX');
        $restingHeartRate = $restingHeartRate ?: $this->Configuration->get('data.HF_REST');

        return $this->LegacyUnitConverter->getHeartRateUnit(
            $this->Configuration->get('general.HEART_RATE_UNIT'),
            $maximalHeartRate,
            $restingHeartRate
        );
    }

    /**
     * @param null|int $maximalHeartRate [bpm]
     * @return PercentMaximum
     */
    public function getHeartRateUnitPercentMaximum($maximalHeartRate = null)
    {
        return new PercentMaximum($maximalHeartRate ?: $this->Configuration->get('data.HF_MAX'));
    }

    /**
     * @return \Runalyze\Metrics\Temperature\Unit\AbstractTemperatureUnit
     */
    public function getTemperatureUnit()
    {
        return $this->LegacyUnitConverter->getTemperatureUnit(
            $this->Configuration->get('general.TEMPERATURE_UNIT')
        );
    }

    /**
     * @return \Runalyze\Metrics\Weight\Unit\AbstractWeightUnit
     */
    public function getWeightUnit()
    {
        return $this->LegacyUnitConverter->getWeightUnit(
            $this->Configuration->get('general.WEIGHT_UNIT')
        );
    }

    /**
     * @param null|float $correctionFactor
     * @return Factorial
     */
    public function getVO2maxUnit($correctionFactor = null)
    {
        return new Factorial('', $correctionFactor ?: $this->Configuration->getVO2maxCorrectionFactor(), 2);
    }
}
