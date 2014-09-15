<?php
/**
 * This file contains class::ConfigurationValueBool
 * @package Runalyze\System\Configuration\Value
 */
/**
 * ConfigurationValueBool
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ConfigurationValueBool extends ConfigurationValue {
	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set( ($valueAsString == 'true') );
	}

	/**
	 * Value as string
	 * @return string
	 */
	public function valueAsString() {
		return ($this->value() ? 'true' : 'false');
	}

	/**
	 * Set from post
	 * Can be overwritten in subclass 
	 */
	public function setFromPost() {
		if (isset($_POST[$this->key().'_sent'])) {
			$this->HasChanged = false;

			$this->set( isset($_POST[$this->key()]));
		}
	}

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		$Field = new FormularCheckbox($this->key(), $this->label(), $this->value());
		$Field->addHiddenSentValue();

		if (!empty($this->Options['layout']))
			$Field->setLayout($this->Options['layout']);

		return $Field;
	}
}