<?php
/**
 * This file contains class::StrideLength
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

/**
 * StrideLength
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class StrideLength {
	/**
	 * Value [cm]
	 * @var int
	 */
	protected $value;

	/**
	 * Format value
	 * @param int $strideLengthInCM [cm]
	 * @return string
	 */
	public static function format($strideLengthInCM) {
		$Object = new StrideLength($strideLengthInCM);

		return $Object->string();
	}

	/**
	 * Constructor
	 * @param int $valueInCM
	 */
	public function __construct($valueInCM) {
		$this->value = $valueInCM > 0 ? (int)$valueInCM : 0;
	}

	/**
	 * Stride length as string
	 * @return string
	 */
	public function string() {
		return $this->asM();
	}

	/**
	 * Value as string [x.xx m]
	 * @return string
	 */
	public function value() {
		return sprintf('%1.2f', $this->inM());
	}

	/**
	 * As m
	 * @return string
	 */
	public function asM() {
		return sprintf('%1.2f', $this->inM()).'&nbsp;m';
	}

	/**
	 * As cm
	 * @return string
	 */
	public function asCM() {
		return $this->inCM().'&nbsp;cm';
	}

	/**
	 * Value in [m]
	 * @return float
	 */
	public function inM() {
		return round($this->value/100, 2);
	}

	/**
	 * Value in [%HRmax]
	 * @return int
	 */
	public function inCM() {
		return round($this->value);
	}
}