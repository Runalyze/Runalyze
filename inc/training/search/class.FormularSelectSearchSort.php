<?php
/**
 * This file contains class::FormularSelectSearchSort
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a double field for sort value and order
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularSelectSearchSort extends FormularField {
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

		$sortByOptions = array(
			'time'		=> 'Datum',
			'distance'	=> 'Distanz',
			's'			=> 'Dauer',
			'pace'		=> 'Pace',
			'elevation'	=> 'H&ouml;henmeter',
			'pulse_avg'	=> 'Puls',
			'temperature'	=> 'Temperatur',
			'vdot'		=> 'VDOT'
		);

		$code .= HTML::selectBox('search-sort-by', $sortByOptions);
		$code .= HTML::selectBox('search-sort-order', array('DESC' => 'absteigend', 'ASC' => 'aufsteigend'));

		$code .= '</div>';

		return $code;
	}
}