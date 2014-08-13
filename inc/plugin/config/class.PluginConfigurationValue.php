<?php
/**
 * This file contains class::PluginConfigurationValue
 * @package Runalyze\Plugin
 */
/**
 * Plugin configuration value
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginConfigurationValue {
	/**
	 * Max length
	 * @var int
	 */
	const MAXLENGTH = 255;

	/**
	 * Key
	 * @var string
	 */
	protected $Key;

	/**
	 * Value
	 * @var mixed
	 */
	protected $Value;

	/**
	 * Default value
	 * @var mixed
	 */
	protected $DefaultValue;

	/**
	 * Label
	 * @var string
	 */
	protected $Label;

	/**
	 * Tooltip
	 * @var string
	 */
	protected $Tooltip;

	/**
	 * Construct
	 * @param string $Key
	 * @param string $Label [optional]
	 * @param string $Tooltip [optional]
	 */
	public function __construct($Key, $Label = '', $Tooltip = '', $DefaultValue = '') {
		$this->Key = $Key;
		$this->Label = $Label;
		$this->Tooltip = $Tooltip;
		$this->DefaultValue = $DefaultValue;
	}

	/**
	 * Key
	 * @return string
	 */
	final public function key() {
		return $this->Key;
	}

	/**
	 * Value
	 * @return mixed
	 */
	final public function value() {
		return $this->Value;
	}

	/**
	 * Set label
	 * @param string $Label
	 */
	final public function setLabel($Label) {
		$this->Label = $Label;
	}

	/**
	 * Set tooltip
	 * @param string $Tooltip
	 */
	final public function setTooltip($Tooltip) {
		$this->Tooltip = $Tooltip;
	}

	/**
	 * Label for form field
	 * @return string
	 */
	final protected function formLabel() {
		if (!empty($this->Tooltip)) {
			return Ajax::tooltip($this->Label, $this->Tooltip, true);
		}

		return $this->Label;
	}

	/**
	 * Set value
	 * @param mixed $Value
	 */
	final public function setValue($Value) {
		$this->Value = $Value;
	}

	/**
	 * Set default value
	 * @param mixed $Value
	 */
	final public function setDefaultValue($Value) {
		$this->DefaultValue = $Value;
	}

	/**
	 * Set default value as value
	 */
	final public function setDefaultValueAsValue() {
		$this->Value = $this->DefaultValue;
	}

	/**
	 * Set value from string
	 * 
	 * Has to be overwritten in subclasses.
	 * @param string $Value
	 */
	public function setValueFromString($Value) {
		$this->Value = $Value;
	}

	/**
	 * Get value as string
	 * @return string
	 */
	public function valueAsString() {
		return $this->value();
	}

	/**
	 * Set value from post
	 */
	public function setValueFromPost() {
		if (isset($_POST[$this->Key])) {
			$this->setValueFromString($_POST[$this->Key]);
		}
	}

	/**
	 * Display row for config form
	 * @return FormularField
	 */
	public function getFormField() {
		$Field = new FormularInput($this->Key, $this->formLabel(), $this->valueAsString());
		$Field->setSize( FormularInput::$SIZE_MIDDLE );

		return $Field;
	}
}