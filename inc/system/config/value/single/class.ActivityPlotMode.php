<?php
/**
 * This file contains class::ActivityPlotMode
 * @package Runalyze\System\Configuration\Value
 */
/**
 * Activity plot mode
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ActivityPlotMode extends ConfigurationValueSelect {
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
	 * @param string $Key
	 */
	public function __construct($Key) {
		parent::__construct($Key, array(
			'default'		=> self::SEPERATED,
			'label'			=> __('Plots: combination'),
			'options'		=> array(
				self::SEPERATED			=> __('all seperated'),
				self::PACE_HR			=> __('Pace / Heart rate'),
				self::PACE_HR_ELEVATION	=> __('Pace / Heart rate / Elevation')
			),
			'onchange'		=> Ajax::$RELOAD_TRAINING
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