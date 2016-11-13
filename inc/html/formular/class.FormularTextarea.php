<?php
/**
 * This file contains class::FormularTextarea
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a standard textarea
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularTextarea extends FormularField {
	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		$this->addAttribute('name', $this->name);
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		return '<label for="'.$this->name.'">'.$this->label.'</label> <textarea '.$this->attributes().'>'.$this->value.'</textarea>';
	}

	/**
	 * Size size for this textarea field
	 * @param string $size 
	 */
	public function setSize($size) {
		$this->addCSSclass($size);
	}
}
