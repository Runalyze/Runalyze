<?php
/**
 * This file contains class::ConfigurationValueSelect
 * @package Runalyze\System\Configuration\Value
 */
/**
 * ConfigurationValueSelect
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ConfigurationValueSelect extends ConfigurationValue {
	/**
	 * Set value
	 * @param mixed $value new value
	 * @throws InvalidArgumentException
	 */
	public function set($value) {
		if (in_array($value, array_keys($this->Options['options']))) {
			parent::set($value);
		} else {
			throw new InvalidArgumentException('Invalid option ("'.$value.'") for select value.');
		}
	}

	/**
	 * Get field
	 * @return FormularInput 
	 */
	public function getField() {
		$Field = new FormularSelectBox($this->key(), $this->label(), $this->valueAsString());
		$Field->setOptions( $this->Options['options'] );

		if (!empty($this->Options['layout']))
			$Field->setLayout($this->Options['layout']);

		return $Field;
	}
}