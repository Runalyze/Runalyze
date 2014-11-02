<?php
/**
 * This file contains class::HeartRateUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Heart rate unit
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class HeartRateUnit extends \Runalyze\Parameter\Select {
	/**
	 * BPM
	 * @var string
	 */
	const BPM = 'bpm';

	/**
	 * % HRmax
	 * @var string
	 */
	const HRMAX = 'hfmax';

	/**
	 * % HRreserve
	 * @var string
	 */
	const HRRESERVE = 'hfres';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::HRMAX, array(
			'options'		=> array(
				self::BPM		=> __('absolute value'),
				self::HRMAX		=> __('&#37; HRmax'),
				self::HRRESERVE	=> __('&#37; HRreserve')
			)
		));
	}
	/**
	 * Is bpm?
	 * @return bool
	 */
	public function isBPM() {
		return ($this->value() == self::BPM);
	}

	/**
	 * Is HRmax?
	 * @return bool
	 */
	public function isHRmax() {
		return ($this->value() == self::HRMAX);
	}

	/**
	 * Is HRreserve?
	 * @return bool
	 */
	public function isHRreserve() {
		return ($this->value() == self::HRRESERVE);
	}
}