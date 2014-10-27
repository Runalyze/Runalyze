<?php
/**
 * This file contains class::TwoPartPercental
 * @package Runalyze\Calculation\Scale
 */

namespace Runalyze\Calculation\Scale;

/**
 * Two-part percental scale
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Scale
 */
class TwoPartPercental extends Percental {
	/**
	 * Inflection point
	 * @var float
	 */
	protected $InflectionPoint = 50;

	/**
	 * Set inflection point
	 * Will be mapped to 50.
	 * @param float $inflectionPoint
	 */
	public function setInflectionPoint($inflectionPoint) {
		$this->InflectionPoint = $inflectionPoint;
	}

	/**
	 * @inheritDoc
	 */
	public function transform($input) {
		if ($input < $this->InflectionPoint) {
			$value = ($input - $this->Min)*50/($this->InflectionPoint - $this->Min);
		} else {
			$value = 50 + ($input - $this->InflectionPoint)*50/($this->Max - $this->InflectionPoint);
		}

		return min(100, max(0, $value));
	}
}