<?php
/**
 * This file contains class::Weight
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Configuration;

/**
 * Weight
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Activity
 */
class Weight {
    
	/**
	 * Default pund multiplier
	 * @var double 
	*/
	const POUND_MULTIPLIER = 2.204622;
	
	/**
	 * Default number of decimals
	 * @var int
	 */
	static public $DEFAULT_DECIMALS = 2;
	
	/**
	 * Weight [kg]
	 * @var float
	 */
	protected $Weight;
	
	/**
	 * Preferred weight unit
	 * @var \Runalyze\Parameter\Application\WeightUnit
	 */
	protected $PreferredUnit;
	
	/**
	 * Create weight
	 * @param float $weight [kg]
	 */
	public function __construct($weight = 0) {
		$this->PreferredUnit = \Runalyze\Configuration::General()->weightUnit();
		$this->set($weight);
	}
	
	/**
	 * Format
	 * @param float $weight [kg]
	 * @param int $decimals [optional] number of decimals
	 * @param bool $withUnit [optional] with or without unit
	 * @return string
	 */
	static public function format($weight, $decimals = false, $withUnit = true) {
		$Object = new Weight($weight);
		return $Object->string($decimals, $withUnit);
	}

	/*
	 * Unit
	 * @return string
	 */
	public function unit() {
	    if($this->PreferredUnit->isKG())
		    return 'kg';
		elseif($this->PreferredUnit->isLBS())
		    return 'lbs';

	}

	/**
	 * Set weight
	 * @param float $weight [kg]
	 * @return \Runalyze\Activity\Weight $this-reference
	 */
	public function set($weight) {
		$this->Weight = (float)str_replace(',', '.', $weight);

		return $this;
	}
	
	/**
	 * Format weight as string
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public function string($decimals = false, $withUnit = true) {

		    if($this->PreferredUnit->isKG())
			return $this->stringKG($withUnit);
		    elseif($this->PreferredUnit->isLBS())
			return $this->stringLBS($withUnit);
	}
	
	/**
	 * Weight
	 * @return float [kg]
	 */
	public function kg() {
		return $this->Weight;
	}
	
	/**
	 * Weight
	 * @return float [lbs]
	 */
	public function LBS() {
		return $this->Weight * self::POUND_MULTIPLIER;
	}
	
	/**
	 * String: as kg
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringKG($withUnit = true) {
		return number_format($this->Weight, 0, '', '.').($withUnit ? 'kg' : '');
	}
	
	/**
	 * String: as punds
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringLBS($withUnit = true) {
		return number_format($this->Weight * self::POUND_MULTIPLIER, 0, '', '.').($withUnit ? 'lbs' : '');
	}
	

}