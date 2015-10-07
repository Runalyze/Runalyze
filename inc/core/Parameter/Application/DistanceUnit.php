<?php
/**
 * This file contains class::DistanceUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;
use Runalyze\Configuration;
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
	 * Yard
	 * @var string
	 */
	const FT = 'ft';
        
        
	/**
	 * HM
	 * @var string
	 */
	const M = 'm';
        
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::KM, array(
			'options'		=> array(
				self::KM		=> __('Metric units'),
				self::MILES		=> __('English units')
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
        
        public function elevationUnit() {
            if($this->isKM())
                return self::M;
            elseif($this->isMILES())
                return self::FT;
        }        
}