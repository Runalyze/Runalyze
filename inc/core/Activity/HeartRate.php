<?php
/**
 * This file contains class::HeartRate
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Configuration;

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
	 * Preferred heart rate unit
	 * @var \Runalyze\Parameter\Application\HeartRateUnit
	 */
	protected $PreferredUnit;

	/**
	 * Constructor
	 * @param int $valueInBPM
	 */
	public function __construct($valueInBPM, \Runalyze\Athlete $Athlete = null) {
		$this->value = $valueInBPM;
		$this->Athlete = $Athlete;
		$this->PreferredUnit = Configuration::General()->heartRateUnit();
	}

	/**
	 * Heart rate as string
	 * @return string
	 */
	public function string() {
		if ($this->PreferredUnit->isHRreserve() && $this->canShowInHRrest()) {
			return $this->asHRrest();
		} elseif ($this->PreferredUnit->isHRmax() && $this->canShowInHRmax()) {
			return $this->asHRmax();
		}

		return $this->asBPM();
	}

	/**
	 * As bpm
	 * @return string
	 */
	public function asBPM() {
		return $this->inBPM().'&nbsp;bpm';
	}

	/**
	 * As %HRmax
	 * Check first 'canShowInHRMax'!
	 * @return string
	 */
	public function asHRmax() {
		return $this->inHRmax().'&nbsp;&#37;';
	}

	/**
	 * As %HRrest
	 * Check first 'canShowInHRMax'!
	 * @return string
	 */
	public function asHRrest() {
		return $this->inHRrest().'&nbsp;&#37;';
	}

	/**
	 * Value in [bpm]
	 * @return int
	 */
	public function inBPM() {
		return round($this->value);
	}

	/**
	 * Value in [%HRmax]
	 * @return int
	 */
	public function inHRmax() {
		return round(100 * $this->value / $this->Athlete->maximalHR());
	}

	/**
	 * Value in [%HRrest]
	 * @return int
	 */
	public function inHRrest() {
		return round(100 * ($this->value - $this->Athlete->restingHR()) / ($this->Athlete->maximalHR() - $this->Athlete->restingHR()));
	}

	/**
	 * Value in [%] depending on preferred unit
	 * @return int
	 */
	public function inPercent() {
		if ($this->PreferredUnit->isHRreserve() && $this->canShowInHRrest()) {
			return $this->inHRrest();
		}

		return $this->inHRmax();
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