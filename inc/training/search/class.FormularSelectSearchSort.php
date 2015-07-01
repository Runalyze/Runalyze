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
		$code .= '<div class="full-size left">';

		$sortByOptions = array(
			'time'		=> __('Date'),
			'distance'	=> __('Distance'),
			's'			=> __('Duration'),
			'pace'		=> __('Pace'),
			'elevation'	=> __('Elevation'),
			'pulse_avg'	=> __('avg. Heartrate'),
			'pulse_max'	=> __('max. Heartrate'),
			'temperature'	=> __('Temperature'),
			'cadence'	=> __('Cadence'),
			'stride_length'	=> __('Stride length'),
			'groundcontact'	=> __('Ground contact'),
			'vertical_oscillation'	=> __('Vertical oscillation'),
			'vdot'		=> __('VDOT'),
			'trimp'		=> __('TRIMP'),
			'jd_intensity'	=> __('JDpoints')
		);

		$code .= HTML::selectBox('search-sort-by', $sortByOptions);
		$code .= HTML::selectBox('search-sort-order', array('DESC' => __('descending'), 'ASC' => __('ascending')));

		$code .= '</div>';

		return $code;
	}
}