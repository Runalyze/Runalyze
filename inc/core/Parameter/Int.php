<?php
/**
 * This file contains class::Int
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

/**
 * Int
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class Int extends \Runalyze\Parameter {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set((int)$valueAsString);
	}
}