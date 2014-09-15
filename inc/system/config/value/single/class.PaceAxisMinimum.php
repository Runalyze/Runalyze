<?php
/**
 * This file contains class::PaceAxisMinimum
 * @package Runalyze\System\Configuration\Value
 */
/**
 * Pace axis minimum
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class PaceAxisMinimum extends PaceAxisLimit {
	/**
	 * Label
	 * @return string
	 */
	protected function labelString() {
		return __('Pace: y-axis-minimum');
	}

	/**
	 * Tooltip
	 * @return string
	 */
	protected function tooltipString() {
		return __('Data points below this limit will be ignored. (only for running)');
	}
}