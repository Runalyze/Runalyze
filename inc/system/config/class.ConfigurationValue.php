<?php
/**
 * This file contains class::ConfigurationValue
 * @package Runalyze\System\Configuration
 */
/**
 * ConfigurationValue
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration
 */
abstract class ConfigurationValue {
	/**
	 * Max length
	 */
	const MAX_LENGTH = 255;

	/**
	 * Key
	 * @var string
	 */
	private $Key = '';

	/**
	 * Value
	 * @var mixed
	 */
	private $Value = null;

	/**
	 * Options
	 * @var array
	 */
	protected $Options = array(
		'default'		=> '',
		'label'			=> '',
		'tooltip'		=> '',
		'options'		=> array(), // ConfigurationValueSelect: key => label
		'folder'		=> '', // ConfigValueSelectFile
		'table'			=> '', // ConfigValueSelectDb
		'column'		=> '', // ConfigValueSelectDb
		'extensions'	=> array(), // ConfigurationValueSelectFile
		'onchange'		=> '', // Ajax::$RELOAD_...-flag
		'onchange_eval'	=> '', // onchange: evaluate code
		'unit'			=> '',
		'size'			=> '',
		'layout'		=> ''
	);

	/**
	 * Flag: Has changed?
	 * @var bool
	 */
	protected $HasChanged = false;

	/**
	 * Construct a new config value
	 * @param string $Key
	 * @param array $Options 
	 */
	public function __construct($Key, $Options = array()) {
		$this->Key = $Key;
		$this->Options = array_merge($this->Options, $Options);

		$this->set( $this->Options['default'] );
	}

	/**
	 * Key
	 * @return string
	 */
	final public function key() {
		return $this->Key;
	}

	/**
	 * Set value
	 * @param mixed $value new value
	 */
	public function set($value) {
		if ($value !== $this->Value) {
			$this->HasChanged = true;
			$this->Value = $value;
		}
	}

	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set($valueAsString);
	}

	/**
	 * Value
	 * @return mixed
	 */
	final public function value() {
		return $this->Value;
	}

	/**
	 * Value as string
	 * @return string
	 */
	public function valueAsString() {
		return (string)$this->Value;
	}

	/**
	 * Has changed?
	 * @return bool
	 */
	final public function hasChanged() {
		return $this->HasChanged;
	}

	/**
	 * Set from post
	 * Can be overwritten in subclass 
	 */
	public function setFromPost() {
		if (isset($_POST[$this->Key])) {
			$this->setFromString($_POST[$this->Key]);
		}
	}

	/**
	 * Get label for value
	 * @return string
	 */
	final public function label() {
		$Label = !empty($this->Options['label']) ? $this->Options['label'] : $this->Key;

		if (!empty($this->Options['tooltip']))
			$Label = Ajax::tooltip($Label, $this->Options['tooltip']);

		return $Label;
	}

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		if (!empty($this->Options['unit']) || !empty($this->Options['size']) || !empty($this->Options['layout'])) {
			$Field = new FormularInput($this->key(), $this->label(), $this->valueAsString());
			$Field->setUnit($this->Options['unit']);
			$Field->setSize($this->Options['size']);
			$Field->setLayout($this->Options['layout']);

			return $Field;
		}

		return new FormularInput($this->key(), $this->label(), $this->valueAsString());
	}

	/**
	 * Do jobs after value changed 
	 */
	final public function doOnchangeJobs() {
		if ($this->HasChanged) {
			$this->evaluateOnchangeCode();
			$this->setReloadFlag();
		}
	}

	/**
	 * Evaluate onchange code
	 */
	private function evaluateOnchangeCode() {
		if (!empty($this->Options['onchange_eval'])) {
			eval($this->Options['onchange_eval']);
		}
	}

	/**
	 * Set reload flag
	 */
	private function setReloadFlag() {
		if (!empty($this->Options['onchange'])) {
			Ajax::setReloadFlag($this->Options['onchange']);
		}
	}
}