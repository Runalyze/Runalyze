<?php
/**
 * This file contains class::FormularInputHidden
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a standard input field (hidden)
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularInputHidden extends FormularInput {
	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		$this->addAttribute('type', 'hidden');
		$this->addAttribute('name', $this->name);
		$this->addAttribute('value', $this->value);
		$this->setId($this->name);
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		return '<input '.$this->attributes().'>';
	}
}