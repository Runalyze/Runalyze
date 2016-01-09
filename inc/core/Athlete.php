<?php
/**
 * This file contains class::Athlete
 * @package Runalyze
 */

namespace Runalyze;

use \Runalyze\Parameter\Application\Gender;

/**
 * Athlete
 * 
 * @author Hannes Christiansen
 * @package Runalyze
 */
class Athlete {
	/**
	 * Gender
	 * @var \Runalyze\Parameter\Application\Gender
	 */
	protected $Gender;

	/**
	 * Maximal heart rate [bpm]
	 * @var int
	 */
	protected $maximalHR = null;

	/**
	 * Resting heart rate [bpm]
	 * @var int
	 */
	protected $restingHR = null;

	/**
	 * Weight [kg]
	 * @var float
	 */
	protected $weight = null;

	/**
	 * Age [years]
	 * @var int
	 */
	protected $age = null;

	/**
	 * Current VDOT shape
	 * @var float
	 */
	protected $vdot = 0.0;

	/**
	 * Create athlete
	 * @param \Runalyze\Parameter\Application\Gender $Gender [optional]
	 * @param int $maximalHR [optional]
	 * @param int $restingHR [optional]
	 * @param float $weight [optional]
	 * @param int $age [optional]
	 * @param float $vdot [optional]
	 */
	public function __construct(
		Gender $Gender = null,
		$maximalHR = null,
		$restingHR = null,
		$weight = null,
		$age = null,
		$vdot = 0.0
	) {
		$this->Gender = $Gender ?: new Gender();
		$this->maximalHR = $maximalHR;
		$this->restingHR = $restingHR;
		$this->weight = $weight;
		$this->age = $age;
		$this->vdot = $vdot;
	}

	/**
	 * Gender
	 * @return \Runalyze\Parameter\Application\Gender
	 */
	public function gender() {
		return $this->Gender;
	}

	/**
	 * Maximal heart rate
	 * @return int
	 */
	public function maximalHR() {
		return $this->maximalHR;
	}

	/**
	 * Resting heart rate
	 * @return int
	 */
	public function restingHR() {
		return $this->restingHR;
	}

	/**
	 * Weight
	 * @return int
	 */
	public function weight() {
		return $this->weight;
	}

	/**
	 * Age
	 * @return int
	 */
	public function age() {
		return $this->age;
	}

	/**
	 * VDOT shape
	 * @return float
	 */
	public function vdot() {
		return $this->vdot;
	}

	/**
	 * Knows gender
	 * @return bool
	 */
	public function knowsGender() {
		return $this->Gender->hasGender();
	}

	/**
	 * Knows maximal HR
	 * @return bool
	 */
	public function knowsMaximalHeartRate() {
		return (null !== $this->maximalHR);
	}

	/**
	 * Knows resting HR
	 * @return bool
	 */
	public function knowsRestingHeartRate() {
		return (null !== $this->restingHR);
	}

	/**
	 * Knows weight
	 * @return bool
	 */
	public function knowsWeight() {
		return (null !== $this->weight);
	}

	/**
	 * Knows age
	 * @return bool
	 */
	public function knowsAge() {
		return (null !== $this->age);
	}

	/**
	 * Knows VDOT shape
	 * @return bool
	 */
	public function knowsVDOT() {
		return (0.0 !== $this->vdot);
	}
}