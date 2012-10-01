<?php
/**
 * Class: ConfigValueString
 * @author Hannes Christiansen <mail@laufhannes.de>
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

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		return new FormularInput($this->getKey(), $this->getLabel(), $this->getValue());
	}
}