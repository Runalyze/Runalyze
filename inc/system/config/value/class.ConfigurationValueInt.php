<?php
/**
 * This file contains class::ConfigurationValueInt
 * @package Runalyze\System\Configuration\Value
 */
/**
 * ConfigurationValueInt
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ConfigurationValueInt extends ConfigurationValue {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set((int)$valueAsString);
	}
}