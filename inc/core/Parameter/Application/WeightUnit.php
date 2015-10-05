<?php
/**
 * This file contains class::WeightUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Weight Unit
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Parameter\Application
 */
class WeightUnit extends \Runalyze\Parameter\Select {
	/**
	 * KG	
	 * @var string
	 */
	const KG = 'kg';

	/**
	 * Pound
	 * @var string
	 */
	const LBS = 'lbs';
        
	/**
	 * Stone
	 * @var string
	 */
	const ST = 'st';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::KG, array(
			'options'		=> array(
				self::KG		=> __('kilogram'),
				self::LBS		=> __('Pounds'),
                                self::ST		=> __('Stone')
			)
		));
	}
	/**
	 * Is kg?
	 * @return bool
	 */
	public function isKG() {
		return ($this->value() == self::KG);
	}

	/**
	 * Is pound?
	 * @return bool
	 */
	public function isLBS() {
		return ($this->value() == self::LBS);
	}
        
	/**
	 * Is stone?
	 * @return bool
	 */
	public function isST() {
		return ($this->value() == self::ST);
	}
        
        /*
         * Get current user weight unit
         *  @return string
         */
        public function unit() {
            return $this->value();
        }

}