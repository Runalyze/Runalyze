<?php
/**
 * This file contains class::Percental
 * @package Runalyze\Calculation\Scale
 */

namespace Runalyze\Calculation\Scale;

/**
 * Percental scale
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Scale
 */
class Percental implements Scale {
	/**
	 * Minimum
	 * @var float
	 */
	protected $Min = 0;

	/**
	 * Maximum
	 * @var float 
	 */
	protected $Max = 100;

	/**
	 * Set minimum
	 * Will be mapped to 0.
	 * @param float $min
	 */
	public function setMinimum($min) {
		$this->Min = $min;
	}

	/**
	 * Set maximum
	 * Will be mapped to 100.
	 * @param float $max
	 */
	public function setMaximum($max) {
		$this->Max = $max;
	}

	/**
	 * @inheritDoc
	 */
	public function transform($input) {
		if ($this->Max == $this->Min) {
			return 0;
		}

		return min(100, 100 * max(0, $input - $this->Min) / ($this->Max - $this->Min) );
	}
}