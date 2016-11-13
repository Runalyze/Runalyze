<?php
/**
 * This file contains class::ActivityRouteBreak
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Activity route break
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class ActivityRouteBreak extends \Runalyze\Parameter\Select {
	/**
	 * No break
	 * @var string
	 */
	const NO_BREAK = 'no';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct('15', array(
			'options'		=> array( // see Runalyze\View\Leaflet\Activity::findLimitForPauses
				self::NO_BREAK	=> __('never'),
				'15'			=> __('at too big distance (15s)'),
				'30'			=> __('at too big distance (30s)'),
				'60'			=> __('at too big distance (60s)'),
				'120'			=> __('at too big distance (120s)'),
				'240'			=> __('at too big distance (240s)'),
				'300'			=> __('at too big distance (300s)'),
				'600'			=> __('at too big distance (600s)'),
			)
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
