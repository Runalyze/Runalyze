<?php
/**
 * This file contains class::WindSpeed
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

class WindSpeed {
    
	/**
	 * Factor: km => miles
	 * @var double 
	 */
	const MILE_MULTIPLIER = 0.621371192;
	
	/**
	 * Factor: miles => km
	 * @var double 
	 */
	const KM_MULTIPLIER = 1.609344;
	
	/**
	 * Speed unit km/h
	 * @var string
	 */
	const KM_PER_H = 'km/h';
	
	/**
	 * Speed unit mph
	 * @var string
	 */
	const MILES_PER_H = 'mph';
	
	/**
	 * Unit
	 * @var int
	 */
	protected $unit;
	
	/**
	 * Wind Speed in Metric
	 * @var float
	 */
	protected $inMetricUnit;
	
	/**
	 * WindSpeed
	 * @param float $value
	 * @param int $unit
	 */
	public function __construct($value = null, $unit = self::KM_PER_H) {
		$this->setWindSpeed($value, $unit);
	}
	
	/**
	 * Set wind Speed
	 * @param float $value
	 * @param int $unit
	 */
	public function setWindSpeed($value, $unit = null) {
		if (!is_null($unit)) {
			$this->unit = $unit;
		}
		
		$this->inMetricUnit = $this->toMetricFrom($value, $this->unit);
		print_r($this->inMetricUnit);
	}
	
	/**
	 * To metric unit
	 * @param float $value
	 * @param int $unit
	 * @return float
	 */
	protected function toMetricFrom($value, $unit) {
		if (!is_numeric($value)) {
			return null;
		}

		switch ($unit) {
			case self::MILES_PER_H:
				return ($value * self::KM_MULTIPLIER);
		}

		return $value;
	}
	
	/**
	 * From metric unit
	 * @param float $value
	 * @param int $unit
	 * @return float
	 */
	protected function fromMetricTo($value, $unit) {
		if (is_null($value)) {
			return null;
		}

		switch ($unit) {
			case self::MILES_PER_H:
				return $value * self::MILE_MULTIPLIER;
		}

		return $value;
	}
	
	/**
	 * Wind Speed is unknown?
	 * @return bool
	 */
	public function isUnknown() {
		return is_null($this->inMetricUnit);
	}
	
	/**
	 * Value
	 * @return null|int
	 */
	public function value() {
		return $this->fromMetricTo($this->inMetricUnit, $this->unit);
	}

	
	/**
	 * Unit
	 * @return string
	 */
	public function unit() {
		switch ($this->unit) {
			case self::KM_PER_H:
				return 'km/h';
			case self::MILES_PER_H:
				return 'mph';
		}

		return '';
	}
}