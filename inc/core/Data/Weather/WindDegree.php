<?php
/**
 * This file contains class::WindDegree
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

/**
 * Wind Degree
 *
 * @author Hannes Christiansen
 * @author Michael 
 * @package Runalyze\Data\Weather
 */
class WindDegree {
    
	/**
	 * Wind Degree
	 * @var float
	 */
	protected $value;
	
	/**
	 * Wind Degree
	 * @param float $value
	 */
	public function __construct($value = null) {
		$this->setWindDegree($value);
	}
	
	/**
	 * Set wind Degree
	 * @param float $value
	 * @param int $unit
	 */
	public function setWindDegree($value) {
		
		$this->value = $value;
	}
	
	/**
	 * Label for value
	 * @return string
	 */
	public function label() {
	    return __('Wind Degree');
	}
	
	/**
	 * Label for value
	 * @return string
	 */
	public function unit() {
	    return '&deg;';
	}
	
	/**
	 * Value
	 * @return null|int
	 */
	public function value() {
		return $this->value;
	}
	
	/**
	 * Wind Speed is unknown?
	 * @return bool
	 */
	public function isUnknown() {
		return is_null($this->value);
	}
}
