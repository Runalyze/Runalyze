<?php
/**
 * This file contains class::PluginConfigurationValueSelect
 * @package Runalyze\Plugin
 */
/**
 * Plugin configuration value: select
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginConfigurationValueSelect extends PluginConfigurationValue {
	/**
	 * Options
	 * @var array
	 */
	protected $Options = array();

	/**
	 * Set options
	 * @param array $Options
	 */
	public function setOptions(array $Options) {
		$this->Options = $Options;
	}

	/**
	 * Display row for config form
	 * @return FormularField
	 */
	public function getFormField() {
		$Field = new FormularSelectBox($this->Key, $this->formLabel(), $this->valueAsString());
		$Field->setOptions( $this->Options );

		return $Field;
	}
}