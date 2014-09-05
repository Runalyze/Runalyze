<?php
/**
 * This file contains class::ConfigurationValueFloat
 * @package Runalyze\System\Configuration\Value
 */
/**
 * ConfigurationValueFloat
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ConfigurationValueFloat extends ConfigurationValue {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set((float)$valueAsString);
	}
}