<?php
/**
 * Class for a standard password-input field 
 */
class FormularInputPassword extends FormularInput {
	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		parent::prepareForDisplay();
		$this->addAttribute('type', 'password');
	}
}