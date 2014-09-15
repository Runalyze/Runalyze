<?php
/**
 * This file contains class::ActivityRouteBreak
 * @package Runalyze\System\Configuration\Value
 */
/**
 * Activity route break
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ActivityRouteBreak extends ConfigurationValueSelect {
	/**
	 * No break
	 * @var string
	 */
	const NO_BREAK = 'no';

	/**
	 * Construct
	 * @param string $Key
	 */
	public function __construct($Key) {
		parent::__construct($Key, array(
			'default'		=> '15',
			'label'			=> __('Map: interrupt route'),
			'tooltip'		=> __('The gps path can be interrupted in case of <em>jumps</em> (e.g. by car/train/...). '.
								'Finding these jumps is not easy. You can define up to what distance (in seconds by average pace) '.
								'between two data points the path should be continued.'),
			'options'		=> array( // see LeafletTrainingRoute::findLimitForPauses
				self::NO_BREAK	=> __('never'),
				'15'			=> __('at too big distance (15s)'),
				'30'			=> __('at too big distance (30s)'),
				'60'			=> __('at too big distance (60s)'),
				'120'			=> __('at too big distance (120s)'),
				'240'			=> __('at too big distance (240s)'),
				'300'			=> __('at too big distance (300s)'),
				'600'			=> __('at too big distance (600s)'),
			),
			'onchange'		=> Ajax::$RELOAD_TRAINING,
			'onchange_eval'	=> 'System::clearTrainingCache();'
		));
	}

	/**
	 * Never break?
	 * @return bool
	 */
	public function never() {
		return ($this->value() == self::NO_BREAK);
	}
}