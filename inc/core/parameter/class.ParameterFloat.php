<?php
/**
 * This file contains class::ParameterFloat
 * @package Runalyze\Parameter
 */
/**
 * Float
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class ParameterFloat extends Parameter {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set((float)$valueAsString);
	}
}