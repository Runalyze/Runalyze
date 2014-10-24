<?php
/**
 * This file contains class::ActivityRoutePrecision
 * @package Runalyze\Parameter\Application
 */
/**
 * Activity route precision
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class ActivityRoutePrecision extends ParameterSelect {
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct('5', array(
			'options'		=> array( // see LeafletTrainingRoute::prepareLoop
				'1'				=> __('every data point'),
				'2'				=> __('every second data point'),
				'5'				=> __('every fifth data point (recommended)'),
				'10'			=> __('every tenth data point'),
				'20'			=> __('every twentieth data point')
			)
		));
	}
}