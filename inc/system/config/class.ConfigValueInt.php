<?php
/**
 * This file contains class::ConfigValueInt
 * @package Runalyze\System\Config
 */
/**
 * ConfigValueInt
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigValueInt extends ConfigValue {
	/**
	 * Type - should be overwritten by subclass
	 * @var string
	 */
	protected $type = 'int';

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
		$this->Value = (int)$Value;
	}
}