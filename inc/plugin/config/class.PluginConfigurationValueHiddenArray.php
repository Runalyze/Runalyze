<?php
/**
 * This file contains class::PluginConfigurationValueHiddenArray
 * @package Runalyze\Plugin
 */
/**
 * Plugin configuration value: hidden array
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginConfigurationValueHiddenArray extends PluginConfigurationValueArray {
	/**
	 * Display row for config form
	 * @return FormularField
	 */
	public function getFormField() {
		return null;
	}
}