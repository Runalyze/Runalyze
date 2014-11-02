<?php
/**
 * This file contains class::Bool
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

/**
 * Bool
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class Bool extends \Runalyze\Parameter {
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