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
		$code .= '<div class="fullSize left">';

		$From = new FormularInput('date-from', '');
		$From->addCSSclass('pick-a-date');
		$From->hideLabel();

		$To   = new FormularInput('date-to', '');
		$To->addCSSclass('pick-a-date');
		$To->hideLabel();

		$code .= $From->getCode().' bis '.$To->getCode();
		$code .= '</div>';

		return $code;
	}
}