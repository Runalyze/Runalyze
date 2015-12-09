<?php
/**
 * This file contains class::Boolean
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

/**
 * Boolean
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class Boolean extends \Runalyze\Parameter {
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