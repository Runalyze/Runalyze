<?php
/**
 * This file contains class::PluginConfigurationValueBool
 * @package Runalyze\Plugin
 */
/**
 * Plugin configuration value: bool
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginConfigurationValueBool extends PluginConfigurationValue {
	/**
	 * Set value from string
	 * 
	 * Has to be overwritten in subclasses.
	 * @param string $Value
	 */
	public function setValueFromString($Value) {
		$this->Value = ($Value == '1');
	}

	/**
	 * Get value as string
	 * @return string
	 */
	public function valueAsString() {
		return ($this->Value ? '1' : '0');
	}

	/**
	 * Set value from post
	 */
	public function setValueFromPost() {
		$this->setValue( isset($_POST[$this->Key]) );
	}

	/**
	 * Display row for config form
	 * @return FormularField
	 */
	public function getFormField() {
		$Field = new FormularCheckbox($this->Key, $this->formLabel(), $this->valueAsString());

		return $Field;
	}
}
