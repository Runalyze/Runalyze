<?php

namespace Runalyze\Data\Weather;

use Runalyze\Activity;
use Runalyze\Activity\ValueInterface;

class HeatIndex implements ValueInterface
{
    /** @var Activity\Temperature */
    protected $HeatIndexTemperature;

	/**
	 * @param Activity\Temperature $temperature
	 * @param Humidity $humidity
	 */
    public function __construct(Activity\Temperature $temperature = null, Humidity $humidity = null)
    {
        if (null !== $temperature && null !== $humidity) {
            $this->setFrom($temperature, $humidity);
        } else {
            $this->setUnknown();
        }
    }

    /**
     * @param null|float|Activity\Temperature $heatIndex floats are treated as temperature in config's unit
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function set($heatIndex)
    {
        if (null === $heatIndex) {
            $this->setUnknown();
        } elseif ($heatIndex instanceof Activity\Temperature) {
            $this->HeatIndexTemperature = clone $heatIndex;
        } elseif (is_numeric($heatIndex)) {
            $this->HeatIndexTemperature = new Activity\Temperature($heatIndex);
        } else {
            throw new \InvalidArgumentException('Can\'t handle given argument, must be null, numerical or an instance of Temperature.');
        }

        return $this;
    }

    public function setFromWeather(\Runalyze\Data\Weather $weather)
    {
        $this->setFrom(
            new Activity\Temperature($weather->temperature()->value()),
            $weather->humidity()
        );
    }

	/**
	 * @param Activity\Temperature $temperature
	 * @param Humidity $humidity
	 */
    public function setFrom(Activity\Temperature $temperature, Humidity $humidity)
    {
        if (null !== $temperature->value() && !$humidity->isUnknown()) {
            $this->HeatIndexTemperature = clone $temperature;
            $this->HeatIndexTemperature->setFahrenheit(
                $this->calculateHeatIndexFor($temperature->fahrenheit(), $humidity->value())
            );
        } else {
            $this->setUnknown();
        }
    }

    public function setUnknown()
    {
        $this->HeatIndexTemperature = new Activity\Temperature();
    }

    /**
     * Calculate heat index
     *
     * Rothfusz regression is used in general, Steadman's simpler formula for
     * temperatures below 80°F.
     *
     * @see http://www.wpc.ncep.noaa.gov/html/heatindex_equation.shtml
     *
     * @param float $temperatureInFahrenheit [°F]
     * @param int $humidity [0 .. 100]
     * @return float [°F]
     */
    protected function calculateHeatIndexFor($temperatureInFahrenheit, $humidity)
    {
        $t = $temperatureInFahrenheit;
        $h = $humidity;

        if ($t < 80) {
            return 1.1 * $t - 10.3 + 0.047 * $h;
        }

        return -42.379 +
            (2.04901523  * $t) +
            (10.14333127 * $h) +
            (-0.22475541 * $t * $h) +
            (-0.00683783 * $t * $t) +
            (-0.05481717 * $h * $h) +
            (0.00122874  * $t * $t * $h) +
            (0.00085282  * $t * $h * $h) +
            (-0.00000199 * $t * $t * $h * $h);
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function label()
    {
        return __('Heat index');
    }

    /**
     * @return null|float [°C|°F|K] temperature unit depends on original object
     */
    public function value()
    {
        return $this->HeatIndexTemperature->valueInPreferredUnit();
    }

    /**
     * @return bool
     */
    public function isUnknown()
    {
        return $this->HeatIndexTemperature->isEmpty();
    }

    /**
     * @return string
     */
    public function unit()
    {
        return $this->HeatIndexTemperature->unit();
    }

    /**
     * @param bool $withUnit
     * @return string
     */
    public function string($withUnit = true)
    {
        return $this->HeatIndexTemperature->string($withUnit);
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getEffectOfHeatIndex()
    {
        $level = HeatIndexEffect::levelFor($this->HeatIndexTemperature->fahrenheit());

        return HeatIndexEffect::label($level);
    }

    /**
     * @return string html code
     * @codeCoverageIgnore
     */
    public function getIcon()
    {
        $level = HeatIndexEffect::levelFor($this->HeatIndexTemperature->fahrenheit());

        return HeatIndexEffect::icon($level);
    }
}
