<?php
/**
 * This file contains class::WindChillFactor
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

use Runalyze\Activity\Temperature as ActivityTemperature;
use Runalyze\Activity\Pace;
use Runalyze\Activity\ValueInterface;

/**
 * Wind chill
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Data\Weather
 */
class WindChillFactor implements ValueInterface
{
    /** @var \Runalyze\Activity\Temperature */
    protected $AdjustedTemperature;

    /**
     * Construct WindChillFactor
     * @param \Runalyze\Data\Weather\WindSpeed $windSpeed
     * @param \Runalyze\Activity\Temperature $temperature
     * @param null|\Runalyze\Activity\Pace $activitySpeed
     */
    public function __construct(WindSpeed $windSpeed, ActivityTemperature $temperature, Pace $activitySpeed = null)
    {
        $this->setFrom($windSpeed, $temperature, $activitySpeed);
    }

    /**
     * Set adjusted temperature
     *
     * Attention: No calculation will be done!
     * @param float|string|null $temperature [°C]
     * @return \Runalyze\Data\Weather\WindChillFactor $this-reference
     */
    public function set($temperature)
    {
        $this->AdjustedTemperature->set($temperature);
    }

    /**
     * Calculate and set adjusted temperature
     * @param \Runalyze\Data\Weather\WindSpeed $windSpeed
     * @param \Runalyze\Activity\Temperature $temperature
     * @param \Runalyze\Activity\Pace $activitySpeed
     * @param null|\Runalyze\Activity\Pace $activitySpeed
     * @throws \InvalidArgumentException
     * @see https://en.wikipedia.org/wiki/Wind_chill#North_American_and_United_Kingdom_wind_chill_index
     */
    public function setFrom(WindSpeed $windSpeed, ActivityTemperature $temperature, Pace $activitySpeed = null)
    {
        if ($windSpeed->isUnknown() || $temperature->isEmpty()) {
            throw new \InvalidArgumentException('Wind speed and temperature must be known. Null value(s) given.');
        }

        $kmh = $windSpeed->inKilometerPerHour();
        $calc = $temperature->celsius();

        if (null !== $activitySpeed) {
            $kmh = $windSpeed->inKilometerPerHour() + $activitySpeed->asKmPerHour();
        }

        if ($kmh >= 5) {
            $calc = 13.12 + 0.6215 * $temperature->celsius() - 11.37 * pow($kmh, 0.16) + 0.3965 * $temperature->celsius() * pow($kmh, 0.16);
        }

        $this->AdjustedTemperature = clone $temperature;
        $this->AdjustedTemperature->set($calc);
    }

    /**
     * Label for value
     * @return string
     */
    public function label()
    {
        return __('Wind chill factor');
    }

    /**
     * Label for value
     * @return string
     */
    public function unit()
    {
        return $this->AdjustedTemperature->unit();
    }

    /**
     * Value
     * @return float|null [°C]
     */
    public function value()
    {
        return $this->AdjustedTemperature->value();
    }

    /**
     * Value in preferred unit
     *
     * Uses the same unit as the initially given temperature object.
     * @return float|null [mixed unit]
     */
    public function valueInPreferredUnit()
    {
        return $this->AdjustedTemperature->valueInPreferredUnit();
    }

    /**
     * @return \Runalyze\Activity\Temperature
     */
    public function adjustedTemperature()
    {
        return $this->AdjustedTemperature;
    }

    /**
     * Format temperature as string
     * @param bool $withUnit [optional] show unit?
     * @param bool|int $decimals [optional] number of decimals
     * @return string
     */
    public function string($withUnit = true, $decimals = 0) {
        return $this->AdjustedTemperature->string($withUnit, $decimals);
    }
}