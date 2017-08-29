<?php
/**
 * This file contains class::FormularInputDate
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a standard date-input field
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularInputTime extends FormularInput {
	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
        $this->addAttribute('type', 'time');

		parent::prepareForDisplay();
	}
}
