<?php
/**
 * This file contains class::ActivityPlotMode
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Activity plot mode
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class ActivityPlotMode extends \Runalyze\Parameter\Select {
	/**
	 * All seperated
	 * @var string
	 */
	const SEPERATED = 'all';

	/**
	 * Pace + Heart rate
	 * @var string
	 */
	const PACE_HR = 'pacepulse';

	/**
	 * Pace + Heart rate + Elevation
	 * @var string
	 */
	const PACE_HR_ELEVATION = 'collection';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::SEPERATED, array(
			'options'		=> array(
				self::SEPERATED			=> __('all seperated'),
				self::PACE_HR			=> __('Pace / Heart rate'),
				self::PACE_HR_ELEVATION	=> __('Pace / Heart rate / Elevation')
			)
		));
	}

	/**
	 * Show seperated
	 * @return bool
	 */
	public function showSeperated() {
		return ($this->value() == self::SEPERATED);
	}

	/**
	 * Show pace and heart rate
	 * @return bool
	 */
	public function showPaceAndHR() {
		return ($this->value() == self::PACE_HR);
	}

	/**
	 * Show pace and heart rate and elevation
	 * @return bool
	 */
	public function showCollection() {
		return ($this->value() == self::PACE_HR_ELEVATION);
	}
}