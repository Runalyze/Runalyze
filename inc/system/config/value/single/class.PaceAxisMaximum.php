<?php
/**
 * This file contains class::PaceAxisMaximum
 * @package Runalyze\System\Configuration\Value
 */
/**
 * Pace axis maximum
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class PaceAxisMaximum extends PaceAxisLimit {
	/**
	 * Label
	 * @return string
	 */
	protected function labelString() {
		return __('Pace: y-axis-maximum');
	}

	/**
	 * Tooltip
	 * @return string
	 */
	protected function tooltipString() {
		return __('Data points above this limit will be ignored. (only for running)');
	}
}