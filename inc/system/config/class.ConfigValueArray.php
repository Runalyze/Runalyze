<?php
/**
 * Class: ConfigValueArray
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ConfigValueArray extends ConfigValue {
	/**
	 * Type - should be overwritten by subclass
	 * @var string
	 */
	protected $type = 'array';

	/**
	 * Get value as string, should be overwritten
	 * @return string
	 */
	protected function getValueAsString() {
		return implode(', ', $this->Value);
	}

	/**
	 * Set value from string, should be overwritten
	 * @param string $Value 
	 */
	protected function setValueFromString($Value) {
		$this->Value = explode(',', $Value);

		foreach ($this->Value as $k => $v)
			$this->Value[$k] = trim($v);
	}

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		return new FormularInput($this->getKey(), $this->getLabel(), $this->getValueAsString());
	}
}