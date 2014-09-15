<?php
/**
 * This file contains class::ActivityRoutePrecision
 * @package Runalyze\System\Configuration\Value
 */
/**
 * Activity route precision
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ActivityRoutePrecision extends ConfigurationValueSelect {
	/**
	 * Construct
	 * @param string $Key
	 */
	public function __construct($Key) {
		parent::__construct($Key, array(
			'default'		=> '5',
			'label'			=> __('Map: precision'),
			'tooltip'		=> __('How many data points shoud be displayed?'),
			'options'		=> array( // see LeafletTrainingRoute::prepareLoop
				'1'				=> __('every data point'),
				'2'				=> __('every second data point'),
				'5'				=> __('every fifth data point (recommended)'),
				'10'			=> __('every tenth data point'),
				'20'			=> __('every twentieth data point')
			),
			'onchange'		=> Ajax::$RELOAD_TRAINING,
			'onchange_eval'	=> 'System::clearTrainingCache();'
		));
	}
}