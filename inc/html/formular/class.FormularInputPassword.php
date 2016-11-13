<?php
/**
 * This file contains class::FormularInputPassword
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a standard password-input field
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularInputPassword extends FormularInput {
	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		$this->addAttribute('type', 'password');
		$this->addAttribute('name', $this->name);
		$this->addAttribute('value', $this->value);
		$this->setId($this->name);

		$this->addUnitAndSize();
	}
}
