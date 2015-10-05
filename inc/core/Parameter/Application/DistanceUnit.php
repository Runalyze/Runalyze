<?php
/**
 * This file contains class::DistanceUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Distance Unit
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Parameter\Application
 */
class DistanceUnit extends \Runalyze\Parameter\Select {
	/**
	 * KM
	 * @var string
	 */
	const KM = 'km';

	/**
	 * MILES
	 * @var string
	 */
	const MILES = 'mi';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::KM, array(
			'options'		=> array(
				self::KM		=> __('Kilometer'),
				self::MILES		=> __('Miles')
			)
		));
	}
	/**
	 * Is kilometers?
	 * @return bool
	 */
	public function isKM() {
		return ($this->value() == self::KM);
	}

	/**
	 * Is Miles?
	 * @return bool
	 */
	public function isMILES() {
		return ($this->value() == self::MILES);
	}
        
        
        /*
         * Get current user distance unit
         *  @return string
         */
        public function unit() {
            return $this->value();
        }

}