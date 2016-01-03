<?php
/**
 * This file contains class::WindSpeed
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;
use Runalyze\Configuration;
use Runalyze\Parameter\Application\DistanceUnitSystem;

/**
 * Wind Speed
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Data\Weather
 */
class WindSpeed {
    
	/**
	 * Factor: km => miles
	 * @var double 
	 */
	const MILE_MULTIPLIER = 0.621371192;
	
	const KM_MULTIPLIER = 1.60934;
	
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
	 * Wind Speed in Metric
	 * @var float
	 */
	protected $inMetricUnit;
	
	/**
	 * @var \Runalyze\Parameter\Application\DistanceUnitSystem 
	 */
	protected $UnitSystem;
	
	/**
	 * WindSpeed
	 * @param float $value
	 * @param \Runalyze\Parameter\Application\DistanceUnitSystem $unitSystem
	 */
	public function __construct($value = null, DistanceUnitSystem $unitSystem = null) {
		$this->set($value);
		$this->UnitSystem = (null !== $unitSystem) ? $unitSystem : Configuration::General()->distanceUnitSystem();
	}
	
	/**
	 * @return float [mixed unit]
	 */
	public function valueInPreferredUnit()
	{
		if ($this->UnitSystem->isImperial()) {
			return $this->imperial();
		}
		return $this->inMetricUnit;
	}
	
	/**
	 * @return float [mixed unit]
	 */
	public function unitInPreferredUnit()
	{
		if ($this->UnitSystem->isImperial()) {
			return self::MILES_PER_H;
		}

		return self::KM_PER_H;
	}
	
	/**
	 * Label for value
	 * @return string
	 */
	public function label() {
	    return __('Wind Speed');
	}
	
	/**
	 * Set wind Speed
	 * @param float $value
	 * @param int $unit
	 */
	public function set($value) {
		$this->inMetricUnit = $value;
	}
	
	
	/**
	 * Set wind Speed from mph in metric
	 * @param float $value
	 * @param int $unit
	 */
	public function setImperial($value) { 
	    echo $value;
		$this->inMetricUnit = $value * self::KM_MULTIPLIER;
	}	
	/**
	 * @param float $windspeed [mixed unit]
	 * @return \Runalyze\Activity\Elevation $this-reference
	 */
	public function setInPreferredUnit($windspeed)
	{
	    echo $value;
		if ($this->UnitSystem->isImperial()) {
			$this->setImperial($windspeed);
		} else {
			$this->set($windspeed);
		}

		return $this;
	}
	
	/**
	 * Get in imperial
	 * @param float $value in Metric
	 */
	public function imperial($value = NULL) {
		if(isset($value)) {
		    return $value * self::MILE_MULTIPLIER;
		} else {
		    return $this->inMetricUnit * self::MILE_MULTIPLIER;
		}
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
		return $this->inMetricUnit;
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