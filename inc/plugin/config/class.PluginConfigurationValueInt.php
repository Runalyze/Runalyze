<?php
/**
 * This file contains class::PluginConfigurationValueInt
 * @package Runalyze\Plugin
 */
/**
 * Plugin configuration value: int
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginConfigurationValueInt extends PluginConfigurationValue {
	/**
	 * Set value from string
	 * 
	 * Has to be overwritten in subclasses.
	 * @param string $Value
	 */
	public function setValueFromString($Value) {
		$this->Value = (int)$Value;
	}

	/**
	 * Display row for config form
	 * @return FormularField
	 */
	public function getFormField() {
		$Field = new FormularInput($this->Key, $this->formLabel(), $this->valueAsString());
		$Field->setSize( FormularInput::$SIZE_SMALL );

		return $Field;
	}
}
