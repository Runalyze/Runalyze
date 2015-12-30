<?php
/**
 * This file contains class::Pressure
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

/**
 * Pressure
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Data\Weather
 */
class Pressure {
    
	/**
	 * Pressure
	 * @var float
	 */
	protected $value;
	
	/**
	 * Construct Pressure
	 * @param float $value
	 */
	public function __construct($value = null) {
		$this->set($value);
	}
    
	/**
	 * Set Pressure
	 * @param float $value
	 * @param int $unit
	 */
	public function set($value) {
		
		$this->value = $value;
	}
	#
	/**
	 * Label for value
	 * @return string
	 */
	public function label() {
	    return __('Pressure');
	}
	
	
	/**
	 * Label for value
	 * @return string
	 */
	public function unit() {
	    	    return 'hpa';
	}
	
	/**
	 * Value
	 * @return null|int
	 */
	public function value() {
		return $this->value;
	}
	
	/**
	 * Humidity is unknown?
	 * @return bool
	 */
	public function isUnknown() {
		return is_null($this->value);
	}
}