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
class FormularInputDate extends FormularInput {
	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		$this->setUnit( Icon::$CALENDAR );
		$this->addCSSclass('pick-a-date');

		parent::prepareForDisplay();
	}
}
