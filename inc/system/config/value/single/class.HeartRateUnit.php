<?php
/**
 * This file contains class::HeartRateUnit
 * @package Runalyze\System\Configuration\Value
 */
/**
 * Heart rate unit
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class HeartRateUnit extends ConfigurationValueSelect {
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
	 * @param string $Key
	 */
	public function __construct($Key) {
		parent::__construct($Key, array(
			'default'		=> self::HRMAX,
			'label'			=> __('Heart rate unit'),
			'options'		=> array(
				self::BPM		=> __('absolute value'),
				self::HRMAX		=> __('&#37; HRmax'),
				self::HRRESERVE	=> __('&#37; HRreserve')
			),
			'onchange'		=> Ajax::$RELOAD_DATABROWSER
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