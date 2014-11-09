<?php
/**
 * This file contains class::ElevationMethod
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Elevation method
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class ElevationMethod extends \Runalyze\Parameter\Select {
	/**
	 * None
	 * @var string
	 */
	const NONE = 'none';

	/**
	 * Threshold
	 * @var string
	 */
	const THRESHOLD = 'treshold';

	/**
	 * Douglas-Peucker
	 * @var string
	 */
	const DOUGLAS_PEUCKER = 'douglas-peucker';

	/**
	 * Reumann-Witkam
	 * @var string
	 */
	const REUMANN_WITKAM = 'reumann-witkamm';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::THRESHOLD, array(
			'options'		=> array(
				self::NONE				=> __('no smoothing'),
				self::THRESHOLD			=> __('Threshold method'),
				self::DOUGLAS_PEUCKER	=> __('Douglas-Peucker-Algorithm')//,
				//self::REUMANN_WITKAM	=> __('Reumann-Witkam-Algorithm')
			)
		));
	}

	/**
	 * Uses none
	 * @return bool
	 */
	public function usesNone() {
		return ($this->value() == self::NONE);
	}

	/**
	 * Uses: Threshold
	 * @return bool
	 */
	public function usesThreshold() {
		return ($this->value() == self::THRESHOLD);
	}

	/**
	 * Uses: Douglas-Peucker
	 * @return bool
	 */
	public function usesDouglasPeucker() {
		return ($this->value() == self::DOUGLAS_PEUCKER);
	}

	/**
	 * Uses: Reumann-Witkam
	 * @return bool
	 */
	public function usesReumannWitkam() {
		return ($this->value() == self::REUMANN_WITKAM);
	}
}