<?php
/**
 * This file contains class::Integer
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

/**
 * Integer
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class Integer extends \Runalyze\Parameter {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set((int)$valueAsString);
	}
}