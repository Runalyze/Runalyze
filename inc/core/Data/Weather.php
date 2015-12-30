<?php
/**
 * This file contains class::Weather
 * @package Runalyze\Data
 */

namespace Runalyze\Data;

use Runalyze\Data\Weather\Temperature;
use Runalyze\Data\Weather\Condition;
use Runalyze\Data\Weather\WindSpeed;
/**
 * Weather
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data
 */
class Weather {
	/**
	 * Temperature
	 * @var \Runalyze\Data\Weather\Temperature
	 */
	protected $Temperature;

	/**
	 * Condition
	 * @var \Runalyze\Data\Weather\Condition
	 */
	protected $Condition;
	
	/**
	 * WindSpeed
	 * @var \Runalyze\Data\Weather\WindSpeed
	 */
	protected $WindSpeed;
	
	/**
	 * WindDegree
	 * @var \Runalyze\Data\Weather\WindDegree
	 */
	protected $WindDegree;

	/**
	 * Weather
	 * @param \Runalyze\Data\Weather\Temperature $Temperature
	 * @param \Runalyze\Data\Weather\Condition $Condition
	 * @param \Runalyze\Data\Weather\WindSpeed $WindSpeed
	 */
	public function __construct(Temperature $Temperature, Condition $Condition, WindSpeed $WindSpeed) {
		$this->Temperature = $Temperature;
		$this->Condition = $Condition;
		$this->WindSpeed = $WindSpeed;
		//$this->WindDegree = $WindDegree;
	}

	/**
	 * Clone object
	 */
	public function __clone() {
		$this->Temperature = clone $this->Temperature;
		$this->Condition = clone $this->Condition;
		$this->WindSpeed = clone $this->WindSpeed;
		//$this->WindDegree = clone $this->WindDegree;
	}

	/**
	 * Temperature
	 * @return \Runalyze\Data\Weather\Temperature
	 */
	public function temperature() {
		return $this->Temperature;
	}

	/**
	 * Condition
	 * @return \Runalyze\Data\Weather\Condition
	 */
	public function condition() {
		return $this->Condition;
	}
	
	/**
	 * Wind Speed
	 * @return \Runalyze\Data\Weather\WindSpeed
	 */
	public function windSpeed() {
		return $this->WindSpeed();
	}
	
	/**
	 * Wind Degree
	 * @return \Runalyze\Data\Weather\WindDegree
	 */
	public function windDegree() {
		return $this->WindDegree();
	}

	/**
	 * Full string
	 * 
	 * Complete string for the weather conditions with icon, name and temperature.
	 * @return string
	 */
	public function fullString() {
		return $this->Condition->icon()->code().' '.$this->Condition->string().' '.__('at').' '.$this->Temperature->asString();
	}

	/**
	 * Is the weather-data empty?
	 * @return bool
	 */
	public function isEmpty() {
		return (
			$this->Temperature->isUnknown() &&
			$this->Condition->isUnknown() &&
			$this->windSpeed()->isUnknown()
		);
	}
}