<?php
/**
 * This file contains class::TemperatureUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Temperature Unit
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Parameter\Application
 */
class TemperatureUnit extends \Runalyze\Parameter\Select {
	/**
	 * Celsius	
	 * @var string
	 */
	const CELSIUS = '°C';

	/**
	 * Fahrenheit
	 * @var string
	 */
	const FAHRENHEIT = '°F';
        
	/**
	 * Construct
	 * @param string $default
	 */
	public function __construct($default = self::CELSIUS) {
		parent::__construct($default, array(
			'options'		=> array(
				self::CELSIUS   => __('Celsius (°C)'),
				self::FAHRENHEIT	=> __('Fahrenheit (°F)')
			)
		));
	}
	/**
	 * Is celsius?
	 * @return bool
	 */
	public function isCelsius() {
		return ($this->value() == self::CELSIUS);
	}

	/**
	 * Is fahrenheit?
	 * @return bool
	 */
	public function isFahrenheit() {
		return ($this->value() == self::FAHRENHEIT);
	}
        
	/**
	 * Get current user temperature unit
	 * @return string
	 */
	public function unit() {
		return $this->value();
	}
}