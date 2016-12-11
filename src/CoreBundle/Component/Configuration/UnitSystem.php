<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration;

use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Metrics\Cadence;
use Runalyze\Metrics\Distance;
use Runalyze\Metrics\LegacyUnitConverter;
use Runalyze\Metrics\Pace\Unit\AbstractPaceUnit;
use Runalyze\Metrics\Pace\Unit\KilometerPerHour;
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
        $this->PaceUnit = $this->LegacyUnitConverter->getPaceUnit($sport->getSpeed());
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
            return $this->LegacyUnitConverter->getPaceUnit($sport->getSpeed());
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
     * @param int|null $maximalHeartRate
     * @param int|null $restingHeartRate
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
}
