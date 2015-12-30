<?php
/**
 * This file contains class::WindSpeed
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Configuration;

class WindSpeed {
    
	/**
	 * windspeed [km/h]
	 * @var float
	 */
	protected $value;
	
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
	 * Label for value
	 * @return string
	 */
	public function label() {
	    return __('Wind Speed');
	}

	/**
	 * Unit
	 * @return string
	 */
	static public function unit() {
	    if(Configuration::General()->distanceUnitSystem()->isMetric()) {
		return self::KM_PER_H;
	    } elseif(Configuration::General()->distanceUnitSystem()->isImperial()) {
		return self::MILES_PER_H;
	    }
	}
	
	/**
	 * Set value
	 * @param mixed $value
	 * @return \Runalyze\Activity\ValueInterface $this-reference
	 */
	public function set($value) {
	    $this->value = $value;
	}

	/**
	 * Get value
	 * @return mixed
	 */
	public function value() {
	    return $this->value();
	}

	/**
	 * Format value as string
	 * @param bool $withUnit
	 * @return string
	 */
	public function string($withUnit = true) {
	    return $this->value;
	}
}