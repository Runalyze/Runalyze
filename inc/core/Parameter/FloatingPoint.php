<?php
/**
 * This file contains class::FloatingPoint
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

/**
 * FloatingPoint (prev. Float)
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class FloatingPoint extends \Runalyze\Parameter {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set((float)$valueAsString);
	}
}