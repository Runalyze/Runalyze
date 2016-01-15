<?php
/**
 * This file contains class::Weather
 * @package Runalyze\Data
 */

namespace Runalyze\Data;

use Runalyze\Data\Weather\Temperature;
use Runalyze\Data\Weather\Condition;
use Runalyze\Data\Weather\WindSpeed;
use Runalyze\Data\Weather\WindDegree;
use Runalyze\Data\Weather\Humidity;
use Runalyze\Data\Weather\Pressure;
use Runalyze\Data\Weather\WindChillFactor;
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
	 * Humidity
	 * @var \Runalyze\Data\Weather\Humidity
	 */
	protected $Humidity;
	
	/**
	 * Pressure
	 * @var \Runalyze\Data\Weather\Pressure
	 */
	protected $Pressure;
	
	/**
	 * WindChillFactor
	 * @var \Runalyze\Data\Weather\WindChillFactor
	 */
	protected $WindChillFactor;
	
	
	/**
	 * Weather
	 * @param \Runalyze\Data\Weather\Temperature $Temperature
	 * @param \Runalyze\Data\Weather\Condition $Condition
	 * @param \Runalyze\Data\Weather\WindSpeed $WindSpeed
	 * @param \Runalyze\Data\Weather\WindDegree $WindDegree
	 * @param \Runalyze\Data\Weather\Humidity $Humidity
	 * @param \Runalyze\Data\Weather\Pressure $Pressure
	 */
	public function __construct(Temperature $Temperature, Condition $Condition, WindSpeed $WindSpeed, WindDegree $WindDegree, Humidity $Humidity, Pressure $Pressure) {
		$this->Temperature = $Temperature;
		$this->Condition = $Condition;
		$this->WindSpeed = $WindSpeed;
		$this->WindDegree = $WindDegree;
		$this->Humidity = $Humidity;
		$this->Pressure = $Pressure;
	}

	/**
	 * Clone object
	 */
	public function __clone() {
		$this->Temperature = clone $this->Temperature;
		$this->Condition = clone $this->Condition;
		$this->WindSpeed = clone $this->WindSpeed;
		$this->WindDegree = clone $this->WindDegree;
		$this->Humditiy = clone $this->Humidity;
		$this->Pressure = clone $this->Pressure;
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
		return $this->WindSpeed;
	}
	
	/**
	 * Wind Degree
	 * @return \Runalyze\Data\Weather\WindDegree
	 */
	public function windDegree() {
		return $this->WindDegree;
	}
	
	/**
	 * Humidity
	 * @return \Runalyze\Data\Weather\Humidity
	 */
	public function humidity() {
		return $this->Humidity;
	}
	
	/**
	 * Pressure
	 * @return \Runalyze\Data\Weather\Pressure
	 */
	public function pressure() {
		return $this->Pressure;
	}
	
	/**
	 * Full string
	 * 
	 * Complete string for the weather conditions with icon, name and temperature.
	 * @return string
	 */
	public function fullString($isNight = false) {
		$icon = $this->Condition->icon();

		if ($isNight == true) {
			$icon->setAsNight();
		}

		return $icon->code().' '.$this->Condition->string().' '.__('at').' '.$this->Temperature->asString();
	}

	/**
	 * Is the weather-data empty?
	 * @return bool
	 */
	public function isEmpty() {
		return (
			$this->Temperature->isUnknown() &&
			$this->Condition->isUnknown() &&
			$this->WindSpeed->isUnknown() &&
			$this->WindDegree->isUnknown() &&
			$this->Humidity->isUnknown() &&
			$this->Pressure->isUnknown()
		);
	}
}