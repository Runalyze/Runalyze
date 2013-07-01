<?php
/**
 * This file contains class::ConfigValueBool
 * @package Runalyze\System\Config
 */
/**
 * ConfigValueBool
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigValueBool extends ConfigValue {
	/**
	 * Type - should be overwritten by subclass
	 * @var string
	 */
	protected $type = 'bool';

	/**
	 * Get value as string, should be overwritten
	 * @return string
	 */
	protected function getValueAsString() {
		return ($this->Value ? 'true' : 'false');
	}

	/**
	 * Set value from string, should be overwritten
	 * @param string $Value 
	 */
	protected function setValueFromString($Value) {
		$this->Value = ($Value == 'true');
	}

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		$Field = new FormularCheckbox($this->getKey(), $this->getLabel(), $this->getValue());
		$Field->addHiddenSentValue();

		if (!empty($this->Options['layout']))
			$Field->setLayout($this->Options['layout']);

		return $Field;
	}
}