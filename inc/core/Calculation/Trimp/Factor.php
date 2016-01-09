<?php
/**
 * This file contains class::Factor
 * @package Runalyze\Calculation\Trimp
 */

namespace Runalyze\Calculation\Trimp;

use Runalyze\Parameter\Application\Gender;

/**
 * Factor
 * 
 * Based on experiments there are different constants for men (1.92)
 * and women (1.67). The base factor (in general 0.64) is adapted for women
 * and unknown gender to get a normalized value which has the same range
 * for everyone.
 * 
 * For an unknown gender, the mean of men's and women's factor is used.
 * 
 * @see http://fellrnr.com/wiki/TRIMP
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Trimp
 */
class Factor {
	/**
	 * Gender
	 * @var \Runalyze\Parameter\Application\Gender
	 */
	protected $Gender;

	/**
	 * Construct
	 * @param \Runalyze\Parameter\Application\Gender $Gender
	 */
	public function __construct(Gender $Gender) {
		$this->Gender = $Gender;
	}

	/**
	 * Base factor
	 * @return float
	 */
	public function A() {
		if ($this->Gender->isMale()) {
			return 0.64;
		} elseif ($this->Gender->isFemale()) {
			return 0.821776;
		} else {
			return 0.725215;
		}
	}

	/**
	 * Exponent factor
	 * @return float
	 */
	public function B() {
		if ($this->Gender->isMale()) {
			return 1.92;
		} elseif ($this->Gender->isFemale()) {
			return 1.67;
		} else {
			return 1.795;
		}
	}
}