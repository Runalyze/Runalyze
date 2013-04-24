<?php
/**
 * This file contains class::ConfigValueFloat
 * @package Runalyze\System\Config
 */
/**
 * ConfigValueFloat
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigValueFloat extends ConfigValue {
	/**
	 * Type - should be overwritten by subclass
	 * @var string
	 */
	protected $type = 'float';

	/**
	 * Get value as string, should be overwritten
	 * @return string
	 */
	protected function getValueAsString() {
		return (string)$this->Value;
	}

	/**
	 * Set value from string, should be overwritten
	 * @param string $Value 
	 */
	protected function setValueFromString($Value) {
		$this->Value = (float)$Value;
	}
}