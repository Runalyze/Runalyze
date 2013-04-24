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
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		// No attributes needed, HTML-function used
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
		$label  = '<label for="'.$this->name.'">'.$this->label.'</label>';
		$select = HTML::selectBox($this->name, $this->options, $this->value, $this->name.'" class="'.implode(' ', $this->cssClasses));

		return $label.$select;
	}
}