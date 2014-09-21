<?php
/**
 * This file contains class::ParameterInt
 * @package Runalyze\Parameter
 */
/**
 * Int
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class ParameterInt extends Parameter {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set((int)$valueAsString);
	}
}