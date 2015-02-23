<?php
/**
 * This file contains class::FormularSelectBox
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a standard select box
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularSelectBox extends FormularField {
	/**
	 * Array with all possible options
	 * @var array
	 */
	private $options = array();

	/**
	 * Multiple
	 * @var boolean
	 */
	protected $multiple = false;

	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		// No attributes needed, HTML-function used
	}

	/**
	 * Set multiple
	 */
	public function setMultiple() {
		$this->multiple = true;
	}

	/**
	 * Add option to selectBox
	 * @param mixed $key
	 * @param string $text 
	 * @param array $attributes
	 */
	public function addOption($key, $text, $attributes = array()) {
		if (!empty($attributes)) {
			$attributes['text'] = $text;
			$text = $attributes;
		}

		$this->options[$key] = $text;
	}

	/**
	 * Set all options
	 * @param array $options keys are values for selectBox
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		if ($this->multiple)
			$this->addAttribute('multiple', 'multiple');

		$label  = '<label for="'.$this->name.'">'.$this->label.'</label>';
		$select = HTML::selectBox($this->name.($this->multiple?'[]':''), $this->options, $this->value, $this->name.'" '.$this->attributes());

		return $label.$select;
	}
}