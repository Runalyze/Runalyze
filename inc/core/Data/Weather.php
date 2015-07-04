<?php
/**
 * This file contains class::Weather
 * @package Runalyze\Data
 */

namespace Runalyze\Data;

use Runalyze\Data\Weather\Temperature;
use Runalyze\Data\Weather\Condition;

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
	 * Weather
	 * @param \Runalyze\Data\Weather\Temperature $Temperature
	 * @param \Runalyze\Data\Weather\Condition $Condition
	 */
	public function __construct(Temperature $Temperature, Condition $Condition) {
		$this->Temperature = $Temperature;
		$this->Condition = $Condition;
	}

	/**
	 * Clone object
	 */
	public function __clone() {
		$this->Temperature = clone $this->Temperature;
		$this->Condition = clone $this->Condition;
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
			$this->Condition->isUnknown()
		);
	}
}