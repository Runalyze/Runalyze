<?php
/**
 * This file contains class::FormularInputSearchTimeRange
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a double field for time range
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularInputSearchTimeRange extends FormularField {
	/**
	 * Validate value
	 * @return boolean
	 */
	public function validate() {
		return true;
	}

	/**
	 * Get code for displaying the field
	 * @return string
	 */
	protected function getFieldCode() {
		$code  = '<label>'.$this->label.'</label>';
		$code .= '<div class="full-size left">';

		$From = new FormularInputDate('date-from', '');
		$From->hideLabel();

		$To = new FormularInputDate('date-to', '');
		$To->hideLabel();

		$code .= $From->getCode().' bis '.$To->getCode();
		$code .= '</div>';

		return $code;
	}
}