<?php
/**
 * This file contains class::HeartRate
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

/**
 * HeartRate
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class HeartRate {
	/**
	 * Value [bpm]
	 * @var int
	 */
	protected $value;

	/**
	 * Athlete
	 * @var \Runalyze\Athlete
	 */
	protected $Athlete;

	/**
	 * Constructor
	 * @param int $valueInBPM
	 */
	public function __construct($valueInBPM, \Runalyze\Athlete $Athlete = null) {
		$this->value = $valueInBPM;
		$this->Athlete = $Athlete;
	}

	/**
	 * Value in [bpm]
	 * @return int
	 */
	public function inBPM() {
		return $this->value;
	}

	/**
	 * Value in [bpm]
	 * @return int
	 */
	public function inHRmax() {
		return round(100 * $this->value / $this->Athlete->maximalHR());
	}

	/**
	 * Value in [bpm]
	 * @return int
	 */
	public function inHRrest() {
		return round(100 * ($this->value - $this->Athlete->restingHR()) / ($this->Athlete->maximalHR() - $this->Athlete->restingHR()));
	}

	/**
	 * Can show in HRmax?
	 * @return bool
	 */
	public function canShowInHRmax() {
		return $this->knowsAthlete() && $this->Athlete->knowsMaximalHeartRate();
	}

	/**
	 * Can show in HRrest?
	 * @return bool
	 */
	public function canShowInHRrest() {
		return $this->knowsAthlete() && $this->Athlete->knowsRestingHeartRate();
	}

	/**
	 * Is the athlete known?
	 * @return bool
	 */
	protected function knowsAthlete() {
		return (NULL !== $this->Athlete);
	}
}