<?php
/**
 * This file contains class::WindDegree
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

/**
 * Humidity
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Data\Weather
 */
class Humidity {
    
	/**
	 * Humidity
	 * @var float
	 */
	protected $value;
	
	/**
	 * Construct Humidity
	 * @param float $value
	 */
	public function __construct($value = null) {
		$this->setHumidity($value);
	}
    
	/**
	 * Set humidity
	 * @param float $value
	 * @param int $unit
	 */
	public function setHumidity($value) {
		
		$this->value = $value;
	}
	#
	/**
	 * Label for value
	 * @return string
	 */
	public function label() {
	    return __('Humidity');
	}
	
	
	/**
	 * Label for value
	 * @return string
	 */
	public function unit() {
	    	    return '&#37;';
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