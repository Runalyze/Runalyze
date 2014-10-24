<?php
/**
 * This file contains class::ParameterBool
 * @package Runalyze\Parameter
 */
/**
 * Bool
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class ParameterBool extends Parameter {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set( ($valueAsString == 'true') );
	}

	/**
	 * Value as string
	 * @return string
	 */
	public function valueAsString() {
		return ($this->value() ? 'true' : 'false');
	}
}