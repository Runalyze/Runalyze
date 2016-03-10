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
	 * Default number of decimals
	 * @var int
	 */
	public static $DefaultDecimals = 0;

	/**
	 * Temperature [°C]
	 * @var float|null
	 */
	protected $Temperature;

	/**
	 * Preferred unit
	 * @var \Runalyze\Parameter\Application\TemperatureUnit
	 */
	protected $PreferredUnit;

	/**
	 * Format
	 * @param float|null $temperature [°C]
	 * @param bool $withUnit [optional] with or without unit
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public static function format($temperature, $withUnit = true, $decimals = false) {
		return (new self($temperature))->string($withUnit, $decimals);
	}

	/**
	 * @param float|null $temperature [°C]
	 * @param \Runalyze\Parameter\Application\TemperatureUnit $preferredUnit
	 */
	public function __construct($temperature = null, TemperatureUnit $preferredUnit = null) {
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
	 * @param float|string|null $temperature [°C]
	 * @return \Runalyze\Activity\Temperature $this-reference
	 */
	public function set($temperature) {
		if (null === $temperature || $temperature === '') {
			$this->Temperature = null;
		} else {
			$this->Temperature = round((float)str_replace(',', '.', $temperature), 2);
		}

		return $this;
	}

	/**
	 * Set temperature in Fahrenheit
	 * @param float|string|null $temperature [°F]
	 * @return \Runalyze\Activity\Temperature $this-reference
	 */
	public function setFahrenheit($temperature) {
		if (null === $temperature || $temperature === '') {
			$this->Temperature = null;
		} else {
			$this->Temperature = round(((float)str_replace(',', '.', $temperature)-32)/1.8, 2);
		}

		return $this;
	}

	/**
	 * @param float|string|null $temperature [mixed unit]
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
	 * @return float|null [°C]
	 */
	public function value() {
		return $this->Temperature;
	}

	/**
	 * Temperature
	 * @return float|null [°C]
	 */
	public function celsius() {
		return $this->Temperature;
	}

	/**
	 * Temperature in fahrenheit
	 * @return float|null [°F]
	 */
	public function fahrenheit() {
		if ($this->isEmpty()) {
			return null;
		}

		return $this->Temperature * 1.8 + 32;
	}

	/**
	 * Is temperature empty?
	 * @return bool
	 */
	public function isEmpty() {
		return (null === $this->Temperature);
	}

	/**
	 * @return float|null [mixed unit]
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
		if ($this->isEmpty()) {
			return '';
		}

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
		if ($this->isEmpty()) {
			return '';
		}

		$decimals = ($decimals === false) ? self::$DefaultDecimals : $decimals;

		return number_format($this->fahrenheit(), $decimals, '.', '').($withUnit ? '&nbsp;'.TemperatureUnit::FAHRENHEIT : '');
	}
}