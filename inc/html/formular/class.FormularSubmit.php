<?php
/**
 * Class for a submit button as field
 */
class FormularSubmit extends FormularField {
	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		$this->addAttribute('type', 'submit');
		$this->addAttribute('value', $this->name);
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		return '<input '.$this->attributes().' />';
	}
}