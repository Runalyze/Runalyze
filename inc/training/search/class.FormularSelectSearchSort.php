<?php
/**
 * This file contains class::FormularSelectSearchSort
 * @package Runalyze\HTML\Formular
 */

use Runalyze\Dataset\Keys;

/**
 * Class for a double field for sort value and order
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularSelectSearchSort extends FormularField {
	/** @var array */
	protected $SortByOptions = [];

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

		$this->addOptionsFor([
			['time', __('Date')],
			Keys::DISTANCE,
			Keys::DURATION,
			['pace', __('Pace')],
			Keys::ELAPSED_TIME,
			Keys::ELEVATION,
			['gradient', __('Gradient')],
            Keys::CLIMB_SCORE,
            Keys::PERCENTAGE_HILLY,
			Keys::HEARTRATE_AVG,
			Keys::HEARTRATE_MAX,
			Keys::TRIMP,
			Keys::RPE,
			Keys::ENERGY,
			Keys::VO2MAX_VALUE,
			Keys::POWER,
			Keys::CADENCE,
			Keys::STRIDE_LENGTH,
			Keys::GROUNDCONTACT,
			Keys::GROUNDCONTACT_BALANCE,
			Keys::VERTICAL_OSCILLATION,
			Keys::VERTICAL_RATIO,
            ['flight_time', __('Flight time')],
            ['flight_ratio', __('Flight ratio')],
            ['avg_impact_gs_left', __('Impact Gs').' ('.__('left').')'],
            ['avg_impact_gs_right', __('Impact Gs').' ('.__('right').')'],
            ['avg_braking_gs_left', __('Braking Gs').' ('.__('left').')'],
            ['avg_braking_gs_right', __('Braking Gs').' ('.__('right').')'],
            ['avg_footstrike_type_left', __('Footstrike type').' ('.__('left').')'],
            ['avg_footstrike_type_right', __('Footstrike type').' ('.__('right').')'],
            ['avg_pronation_excursion_left', __('Pronation excursion').' ('.__('left').')'],
            ['avg_pronation_excursion_right', __('Pronation excursion').' ('.__('right').')'],
			Keys::TOTAL_STROKES,
			Keys::SWOLF,
			Keys::FIT_VO2MAX_ESTIMATE,
			Keys::FIT_RECOVERY_TIME,
			Keys::FIT_HRV_ANALYSIS,
			Keys::FIT_TRAINING_EFFECT,
			Keys::FIT_PERFORMANCE_CONDITION_START,
            Keys::FIT_PERFORMANCE_CONDITION_END,
            Keys::TEMPERATURE,
			['wind_speed', __('Wind speed')],
			['wind_deg', __('Wind direction')],
			Keys::HUMIDITY,
			Keys::AIR_PRESSURE
		]);

		$code .= HTML::selectBox('search-sort-by', $this->SortByOptions);
		$code .= HTML::selectBox('search-sort-order', array('DESC' => __('descending'), 'ASC' => __('ascending')));

		$code .= '</div>';

		return $code;
	}

	/**
	 * @param array $keyIds
	 */
	protected function addOptionsFor(array $keyIds) {
		foreach ($keyIds as $keyIdOrArray) {
			if (is_array($keyIdOrArray)) {
				$this->SortByOptions[$keyIdOrArray[0]] = $keyIdOrArray[1];
			} else {
				$Key = Keys::get($keyIdOrArray);

				if (!is_array($Key->column())) {
    				$this->SortByOptions[$Key->column()] = $Key->label();
                }
			}
		}
	}
}
