<?php
/**
 * This file contains class::Temperature
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\TemperatureUnit;

/**
 * Temperature
 * 
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Activity
 */
class Temperature implements ValueInterface {
        
	/**
	 * Default kelvin multiplier
	 * @var double 
	*/
	const KELVIN_MULTIPLIER = 274.15;

	/**
	 * Default number of decimals
	 * @var int
	 */
	public static $DefaultDecimals = 0;

	/**
	 * Temperature [°C]
	 * @var float
	 */
	protected $Temperature;

	/**
	 * Preferred unit
	 * @var \Runalyze\Parameter\Application\TemperatureUnit
	 */
	protected $PreferredUnit;

	/**
	 * Format
	 * @param float $temperature [°C]
	 * @param bool $withUnit [optional] with or without unit
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public static function format($temperature, $withUnit = true, $decimals = false) {
		return (new self($temperature))->string($withUnit, $decimals);
	}

	/**
	 * @param float $temperature [°C]
	 * @param \Runalyze\Parameter\Application\WeightUnit $preferredUnit
	 */
	public function __construct($temperature = 0, TemperatureUnit $preferredUnit = null) {
		$this->PreferredUnit = (null !== $preferredUnit) ? $preferredUnit : Configuration::General()->temperatureUnit();

		$this->set($temperature);
	}

	public function label() {
		return __('Temperature');
	}

	/**
	 * Unit
	 * @return string
	 */
	public function unit() {
		return $this->PreferredUnit->unit();
	}

	/**
	 * Set temperature
	 * @param float $temperature [°C]
	 * @return \Runalyze\Activity\Temperature $this-reference
	 */
	public function set($temperature) {
		$this->Temperature = (float)str_replace(',', '.', $temperature);

		return $this;
	}

	/**
	 * Set temperature in Fahrenheit
	 * @param float $temperature [°F]
	 * @return \Runalyze\Activity\Temperature $this-reference
	 */
	public function setFahrenheit($temperature) {
		$this->Temperature = ((float)str_replace(',', '.', $temperature)-32)/1.8;

		return $this;
	}

	/**
	 * @param float $temperature [mixed unit]
	 * @return \Runalyze\Activity\Temperature $this-reference
	 */
	public function setInPreferredUnit($temperature) {
		if ($this->PreferredUnit->isFahrenheit()) {
			$this->setFahrenheit($temperature);
		} else {
			$this->set($temperature);
		}

		return $this;
	}

	/**
	 * Format temperature as string
	 * @param bool $withUnit [optional] show unit?
	 * @param bool|int $decimals [optional] number of decimals
	 * @return string
	 */
	public function string($withUnit = true, $decimals = false) {
		if ($this->PreferredUnit->isFahrenheit()) {
			return $this->stringFahrenheit($withUnit, $decimals);
		}

		return $this->stringCelsius($withUnit, $decimals);
	}

	/**
	 * @return float [°C]
	 */
	public function value() {
		return $this->Temperature;
	}

	/**
	 * Weight
	 * @return float [°C]
	 */
	public function celsius() {
		return $this->Temperature;
	}

	/**
	 * Weight in fahrenheit
	 * @return float [°F]
	 */
	public function fahrenheit() {
		return $this->Temperature * 1.8 + 32;
	}

	/**
	 * @return float [mixed unit]
	 */
	public function valueInPreferredUnit() {
		if ($this->PreferredUnit->isFahrenheit()) {
			return $this->fahrenheit();
		}

		return $this->celsius();
	}

	/**
	 * String: as °C
	 * @param bool $withUnit [optional] show unit?
	 * @param bool|int $decimals [optional] number of decimals
	 * @return string with unit
	 */
	public function stringCelsius($withUnit = true, $decimals = false) {
		$decimals = ($decimals === false) ? self::$DefaultDecimals : $decimals;

		return number_format($this->Temperature, $decimals, '.', '').($withUnit ? '&nbsp;'.TemperatureUnit::CELSIUS : '');
	}

	/**
	 * String: as fahrenheit
	 * @param bool $withUnit [optional] show unit?
	 * @param bool|int $decimals [optional] number of decimals
	 * @return string with unit
	 */
	public function stringFahrenheit($withUnit = true, $decimals = false) {
		$decimals = ($decimals === false) ? self::$DefaultDecimals : $decimals;

		return number_format($this->fahrenheit(), $decimals, '.', '').($withUnit ? '&nbsp;'.TemperatureUnit::FAHRENHEIT : '');
	}
}