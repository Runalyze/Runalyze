<?php
/**
 * Class: ConfigValueSelect
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ConfigValueSelect extends ConfigValue {
	/**
	 * Type - should be overwritten by subclass
	 * @var string
	 */
	protected $type = 'select';

	/**
	 * Get value as string, should be overwritten
	 * @return string
	 */
	protected function getValueAsString() {
		return $this->Value;
	}

	/**
	 * Set value from string, should be overwritten
	 * @param string $Value 
	 */
	protected function setValueFromString($Key) {
		$Key         = self::transformOldStringToValue($Key);
		$this->Value = $Key;

		if (!isset($this->Options['options'][$Key]))
			Error::getInstance()->addWarning('Configuration: "'.$Key.'" invalid option for '.$this->Key);
	}

	/**
	 * In Runalyze v1.1 select-values had a different representation in database: Transform from that to new string
	 * @param string $Key 
	 * @return string
	 */
	static private function transformOldStringToValue($Key) {
		if (strpos($Key,'=') === false)
			return $Key;

		$Options = explode('|', $Key);
		foreach ($Options as $Option) {
			$ValueParts = explode('=', $Option);
			if ($ValueParts[1] == 'true')
				return $ValueParts[0];
		}

		return 'unknown';
	}

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		$Field = new FormularSelectBox($this->getKey(), $this->getLabel(), $this->getValue());
		$Field->setOptions( $this->Options['options'] );

		return $Field;
	}
}