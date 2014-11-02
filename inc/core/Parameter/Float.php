<?php
/**
 * This file contains class::Float
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

/**
 * Float
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class Float extends \Runalyze\Parameter {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set((float)$valueAsString);
	}
}