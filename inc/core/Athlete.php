<?php
/**
 * This file contains class::Athlete
 * @package Runalyze
 */

namespace Runalyze;

use Runalyze\Profile\Athlete\Gender;

/**
 * Athlete
 * 
 * @author Hannes Christiansen
 * @package Runalyze
 */
class Athlete {
	/**
	 * Gender
	 * @var int
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
	protected $birthyear = null;

	/**
	 * Current VDOT shape
	 * @var float
	 */
	protected $vdot = 0.0;

	/**
	 * Create athlete
	 * @param int $Gender [optional]
	 * @param int $maximalHR [optional]
	 * @param int $restingHR [optional]
	 * @param float $weight [optional]
	 * @param int $birthyear [optional]
	 * @param float $vdot [optional]
	 */
	public function __construct(
		$Gender = null,
		$maximalHR = null,
		$restingHR = null,
		$weight = null,
		$birthyear = null,
		$vdot = 0.0
	) {
		$this->Gender = $Gender ?: Gender::NONE;
		$this->maximalHR = $maximalHR;
		$this->restingHR = $restingHR;
		$this->weight = $weight;
		$this->birthyear = $birthyear;
		$this->vdot = $vdot;
	}

	/**
	 * Gender
	 * @return \Runalyze\Profile\Athlete\Gender;
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
        return (null !== $this->birthyear()) ? date("Y")-$this->birthyear() : null;
    }

	/**
	 * Birthyear
	 * @return int
	 */
	public function birthyear() {
		return $this->birthyear();
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
		return ($this->Gender !== Gender::NONE && null !== $this->Gender);
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
     * Knows birthyear
     * @return bool
     */
    public function knowsBirthyear() {
        return (null !== $this->birthyear());
    }

	/**
	 * Knows age
	 * @return bool
	 */
	public function knowsAge() {
		return (null !== $this->birthyear());
	}

	/**
	 * Knows VDOT shape
	 * @return bool
	 */
	public function knowsVDOT() {
		return (0.0 !== $this->vdot);
	}
}