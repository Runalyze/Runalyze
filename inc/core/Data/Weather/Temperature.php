<?php
/**
 * This file contains class::Temperature
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

/**
 * Temperature
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class Temperature {
	/**
	 * @var int
	 */
	const CELSIUS = 0;

	/**
	 * @var int
	 */
	const FAHRENHEIT = 1;

	/**
	 * @var int
	 */
	const KELVIN = 2;

	/**
	 * Unit
	 * @var int
	 */
	protected $unit;

	/**
	 * Temperature in celsius
	 * @var float
	 */
	protected $inCelsius;

	/**
	 * Temperature
	 * @param float $value
	 * @param int $unit
	 */
	public function __construct($value = null, $unit = self::CELSIUS) {
		$this->setTemperature($value, $unit);
	}

	/**
	 * Set temperature
	 * @param float $value
	 * @param int $unit
	 */
	public function setTemperature($value, $unit = null) {
		if (!is_null($unit)) {
			$this->unit = $unit;
		}

		$this->inCelsius = $this->toCelsiusFrom($value, $this->unit);
	}

	/**
	 * To celsius
	 * @param float $value
	 * @param int $unit
	 * @return float
	 */
	protected function toCelsiusFrom($value, $unit) {
		if (is_null($value)) {
			return null;
		}

		switch ($unit) {
			case self::FAHRENHEIT:
				return ($value - 32)/1.8;
			case self::KELVIN:
				return $value - 273.15;
		}

		return $value;
	}

	/**
	 * From celsius
	 * @param float $value
	 * @param int $unit
	 * @return float
	 */
	protected function fromCelsiusTo($value, $unit) {
		if (is_null($value)) {
			return null;
		}

		switch ($unit) {
			case self::FAHRENHEIT:
				return $value*1.8 + 32;
			case self::KELVIN:
				return $value + 273.15;
		}

		return $value;
	}

	/**
	 * Set unit to celsius
	 */
	public function toCelsius() {
		$this->unit = self::CELSIUS;
	}

	/**
	 * Set unit to fahrenheit
	 */
	public function toFahrenheit() {
		$this->unit = self::FAHRENHEIT;
	}

	/**
	 * Set unit to kelvin
	 */
	public function toKelvin() {
		$this->unit = self::KELVIN;
	}

	/**
	 * Temperature unknown?
	 * @return bool
	 */
	public function isUnknown() {
		return is_null($this->inCelsius);
	}

	/**
	 * Unit
	 * @return string
	 */
	public function value() {
		return $this->fromCelsiusTo($this->inCelsius, $this->unit);
	}

	/**
	 * As string
	 * @return string
	 */
	public function asString() {
		if ($this->isUnknown()) {
			return '?&nbsp;'.$this->unit();
		}

		return round($this->value()).'&nbsp;'.$this->unit();
	}

	/**
	 * Unit
	 * @return string
	 */
	public function unit() {
		switch ($this->unit) {
			case self::CELSIUS:
				return '&deg;C';
			case self::FAHRENHEIT:
				return '&deg;F';
			case self::KELVIN:
				return 'K';
		}

		return '';
	}
}