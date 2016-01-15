<?php
/**
 * This file contains class::WindSpeed
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

use Runalyze\Activity\ValueInterface;
use Runalyze\Configuration;
use Runalyze\Parameter\Application\DistanceUnitSystem;
use Runalyze\Parameter\Application\PaceUnit;

/**
 * Wind Speed
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Data\Weather
 */
class WindSpeed implements ValueInterface
{
    /**
     * Wind Speed
     * @var float|null [km/h]
     */
    protected $KilometerPerHour;

    /**
     * @var \Runalyze\Parameter\Application\DistanceUnitSystem
     */
    protected $UnitSystem;

    /**
     * Wind speed
     * @param float|null $kilometerPerHour [km/h]
     * @param \Runalyze\Parameter\Application\DistanceUnitSystem $unitSystem
     */
    public function __construct($kilometerPerHour = null, DistanceUnitSystem $unitSystem = null)
    {
        $this->set($kilometerPerHour);
        $this->UnitSystem = (null !== $unitSystem) ? $unitSystem : Configuration::General()->distanceUnitSystem();
    }

    /**
     * @return float [mixed unit]
     */
    public function valueInPreferredUnit()
    {
        if ($this->UnitSystem->isImperial()) {
            return $this->inMilesPerHour();
        }

        return $this->KilometerPerHour;
    }

    /**
     * Label for value
     * @return string
     */
    public function label()
    {
        return __('Wind Speed');
    }

    /**
     * Set wind speed in internal unit
     * @param float $kilometerPerHour [km/h]
     * @return \Runalyze\Data\Weather\WindSpeed $this-reference
     */
    public function set($kilometerPerHour)
    {
        $this->KilometerPerHour = $kilometerPerHour;

        return $this;
    }

    /**
     * Set wind speed in km/h
     * @param float $kilometerPerHour [km/h]
     * @return \Runalyze\Data\Weather\WindSpeed $this-reference
     */
    public function setKilometerPerHour($kilometerPerHour)
    {
        return $this->set($kilometerPerHour);
    }

    /**
     * Set wind speed in mph
     * @param float $milesPerHour [mph]
     * @return \Runalyze\Data\Weather\WindSpeed $this-reference
     */
    public function setMilesPerHour($milesPerHour)
    {
        $this->KilometerPerHour = $milesPerHour / DistanceUnitSystem::MILE_MULTIPLIER;

        return $this;
    }

    /**
     * @param float $windspeed [mixed unit]
     * @return \Runalyze\Data\Weather\WindSpeed $this-reference
     */
    public function setInPreferredUnit($windspeed)
    {
        if ($this->UnitSystem->isImperial()) {
            $this->setMilesPerHour($windspeed);
        } else {
            $this->set($windspeed);
        }

        return $this;
    }

    /**
     * Wind Speed is unknown?
     * @return bool
     */
    public function isUnknown()
    {
        return (null === $this->KilometerPerHour);
    }

    /**
     * Value
     * @return null|float [km/h]
     */
    public function value()
    {
        return $this->KilometerPerHour;
    }

    /**
     * Wind speed in miles per hour
     * @return null|float [km/h]
     */
    public function inKilometerPerHour()
    {
        return $this->KilometerPerHour;
    }

    /**
     * Wind speed in miles per hour
     * @return null|float [mph]
     */
    public function inMilesPerHour()
    {
        return $this->KilometerPerHour * DistanceUnitSystem::MILE_MULTIPLIER;
    }

    /**
     * Unit
     * @return string
     */
    public function unit()
    {
        if ($this->UnitSystem->isImperial()) {
            return PaceUnit::MILES_PER_H;
        }

        return PaceUnit::KM_PER_H;
    }

    /*
     * String
     * @return string
     */
    public function string($withUnit = true, $decimals = 0)
    {
        if ($this->isUnknown()) {
            return '';
        }

        return number_format($this->valueInPreferredUnit(), $decimals).($withUnit ? '&nbsp;'.$this->unit() : '');
    }
}