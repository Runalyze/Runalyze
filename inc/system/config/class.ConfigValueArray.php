<?php
/**
 * This file contains class::ConfigValueArray
 * @package Runalyze\System\Config
 */
/**
 * ConfigValueArray
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
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
		return self::arrayToString($this->Value);
	}

	/**
	 * Transform array to string for internal database structure
	 * @param array $array
	 * @return string
	 */
	static public function arrayToString($array) {
		return implode(', ', $array);
	}

	/**
	 * Set value from string, should be overwritten
	 * @param string $Value 
	 */
	protected function setValueFromString($Value) {
		if (strlen($Value) == 0)
			$this->Value = array();
		else
			$this->Value = explode(',', $Value);

		foreach ($this->Value as $k => $v)
			$this->Value[$k] = trim($v);

		$this->Value = serialize($this->Value);
	}

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		$Field = new FormularInput($this->getKey(), $this->getLabel(), $this->getValueAsString());

		if (!empty($this->Options['layout']))
			$Field->setLayout($this->Options['layout']);

		return $Field;
	}
}