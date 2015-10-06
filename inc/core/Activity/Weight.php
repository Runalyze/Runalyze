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
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Activity
 */
class Weight {
    
	/**
	 * Default poun d multiplier
	 * @var double 
	*/
	const POUND_MULTIPLIER = 2.204622;
        
	/**
	 * Default stone multiplier
	 * @var double 
	*/
	const STONE_MULTIPLIER = 0.157473;
	
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
                elseif($this->PreferredUnit->isST())
		    return 'st';

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
	 * Set pound weight
	 * @param float $weight [pound]
	 * @return \Runalyze\Activity\Weight $this-reference
	 */
	public function setPound($weight) {
		$this->Weight = (float)str_replace(',', '.', $weight) * 0.453592;

		return $this;
	}
        
	/**
	 * Set stone weight
	 * @param float $weight [stone]
	 * @return \Runalyze\Activity\Weight $this-reference
	 */
	public function setStone($weight) {
		$this->Weight = (float)str_replace(',', '.', $weight) * 6.35029;

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
                    elseif($this->PreferredUnit->isST())
			return $this->stringST($withUnit);
	}
	
	/**
	 * Weight
	 * @return float [kg]
	 */
	public function kg() {
		return $this->Weight;
	}
	
	/**
	 * Weight in pounds
	 * @return float [lbs]
	 */
	public function LBS() {
		return $this->Weight * self::POUND_MULTIPLIER;
	}
        
	/**
	 * Weight in stone
	 * @return float [st]
	 */
	public function ST() {
		return $this->Weight * self::STONE_MULTIPLIER;
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
		return number_format($this->LBS(), 0, '', '.').($withUnit ? 'lbs' : '');
	}
        
	/**
	 * String: as stone
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringST($withUnit = true) {
		return number_format($this->ST(), 0, '', '.').($withUnit ? 'st' : '');
	}

}