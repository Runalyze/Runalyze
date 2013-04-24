<?php
/**
 * This file contains class::ConfigValueString
 * @package Runalyze\System\Config
 */
/**
 * ConfigValueString
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigValueString extends ConfigValue {
	/**
	 * Type - should be overwritten by subclass
	 * @var string
	 */
	protected $type = 'string';

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
		$this->Value = (string)$Value;
	}
}