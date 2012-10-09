<?php
/**
 * Class: ConfigValueFloat
 * @author Hannes Christiansen <mail@laufhannes.de>
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

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		return new FormularInput($this->getKey(), $this->getLabel(), $this->getValue());
	}
}