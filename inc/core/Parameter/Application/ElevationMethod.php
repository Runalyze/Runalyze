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
	 * Treshold
	 * @var string
	 */
	const TRESHOLD = 'treshold';

	/**
	 * Douglas-Peucker
	 * @var string
	 */
	const DOUGLAS_PEUCKER = 'douglas-peucker';

	/**
	 * Reumann-Witkamm
	 * @var string
	 */
	const REUMANN_WITKAMM = 'reumann-witkamm';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::TRESHOLD, array(
			'options'		=> array(
				self::NONE				=> __('no smoothing'),
				self::TRESHOLD			=> __('Treshold method'),
				self::DOUGLAS_PEUCKER	=> __('Douglas-Peucker-Algorithm')//,
				//self::REUMANN_WITKAMM	=> __('Reumann-Witkamm-Algorithm')
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
	 * Uses: Treshold
	 * @return bool
	 */
	public function usesTreshold() {
		return ($this->value() == self::TRESHOLD);
	}

	/**
	 * Uses: Douglas-Peucker
	 * @return bool
	 */
	public function usesDouglasPeucker() {
		return ($this->value() == self::DOUGLAS_PEUCKER);
	}

	/**
	 * Uses: Reumann-Witkamm
	 * @return bool
	 */
	public function usesReumannWitkamm() {
		return ($this->value() == self::REUMANN_WITKAMM);
	}
}