<?php
/**
 * Class for a standard password-input field 
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