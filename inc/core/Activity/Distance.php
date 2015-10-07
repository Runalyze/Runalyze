<?php
/**
 * This file contains class::Distance
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;


// TODO: use
// Configuration::ActivityView()->decimals()

/**
 * Distance
 *
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Activity
 */
class Distance {
	/**
	 * Auto format
	 * @var string
	 */
	const FORMAT_AUTO = 'auto';

	/**
	 * Seperator for decimals
	 * @var string
	 */
	static public $DECIMAL_POINT = ',';

	/**
	 * Seperator for thousands
	 * @var string
	 */
	static public $THOUSANDS_POINT = '.';
        
	/**
	 * Default mile multiplier
	 * @var double 
	*/
	const MILE_MULTIPLIER = 0.621371192;
	
	/**
	 * Default yard multiplier
	 * @var double 
	*/
	const YARD_MULTIPLIER = 1093.6133;
	
	/**
	 * Default feet multiplier
	 * @var double
	 */
	const FEET_MULTIPLIER = 3280.84;
       

	/**
	 * Default number of decimals
	 * @var int
	 */
	static public $DEFAULT_DECIMALS = 2;

	/**
	 * Distance [km]
	 * @var float
	 */
	protected $Distance;
	
	/**
	 * Preferred distance unit
	 * @var \Runalyze\Parameter\Application\DistanceUnit
	 */
	protected $PreferredUnit;

	/**
	 * Format
	 * @param float $distance [km]
	 * @param mixed $format [optional] set as true for display as meter, can be 'auto'
	 * @param int $decimals [optional] number of decimals
	 * @param bool $withUnit [optional] with or without unit
	 * @return string
	 */
	static public function format($distance, $format = false, $decimals = false, $withUnit = true) {
		$Object = new Distance($distance);
		return $Object->string($format, $decimals, $withUnit);
	}
	
	/**
	 * Format to m/yard
	 * @param float $distance [km]
	 * @param bool $withUnit [optional] with or without unit
	 * @return string
	 */
	static public function formatYard($distance, $withUnit = true) {
		$Object = new Distance($distance);
		return $Object->stringYards($withUnit);
	}
	
	/**
	 * Format to m/feet
	 * @param float $distance [km]
	 * @param bool $withUnit [optional] with or without unit
	 * @return string
	 */
	static public function formatFeet($distance, $withUnit = true) {
		$Object = new Distance($distance);
		return $Object->stringFeet($withUnit);
	}

	/**
	 * Create distance
	 * @param float $distance [km]
	 */
	public function __construct($distance = 0) {
		$this->PreferredUnit = \Runalyze\Configuration::General()->distanceUnit();
		$this->set($distance);
	}

	/**
	 * Set distance
	 * @param float $distance [km]
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function set($distance) {
		$this->Distance = (float)str_replace(',', '.', $distance);

		return $this;
	}
        
	/**
	 * Set distance in miles
	 * @param float $distance [mi]
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function setMiles($distance) {
		$this->Distance = (float)str_replace(',', '.', $distance) * 1.60934;

		return $this;
	}

	/**
	 * Kilometer
	 * @return float [km]
	 */
	public function kilometer() {
		return $this->Distance;
	}
        
	/**
	 * Meter
	 * @return int [m]
	 */
	public function meter() {
		return round($this->Distance*1000);
	}
        
	 /**
	 * Miles
	 * @return float [miles]
	 */
	public function miles() {
		return $this->multiply(self::MILE_MULTIPLIER);
	}
        
	/*
	 * Yards
	 * @return int [yards]
	*/
	public function yards() {
		return $this->multiply(self::YARD_MULTIPLIER);
	}
        
	/*
	 * Feet
	 * @return int [feet]
	*/
	public function feets() {
		return $this->multiply(self::FEET_MULTIPLIER);
	}
	
	/*
	 * Unit
	 * @return string
	 */
	public function unit($format = false) {
	    if ($format === true) {
                return $this->unitForShortDistances();
	    } else {
		if($this->PreferredUnit->isKM())
		    return 'km';
		elseif($this->PreferredUnit->isMILES())
		    return 'mi';
	    }
	}
        
	/*
	 * Unit for short distances
	 * @return string
	 */
	public function unitForShortDistances() {
	    if($this->PreferredUnit->isKM()) {
		    return 'm';
            } elseif($this->PreferredUnit->isMILES()) {
		    return 'y';
	    }
	}   
	
	/*
	 * Unit for short distances
	 * @return string
	 */
	public function unitForDistancesYard() {
	    $this->unitForShortDistances();
	}   
        
	/*
	 * Unit for short distances
	 * @return string
	 */
	public function unitForDistancesFeet() {
	    if($this->PreferredUnit->isKM()) {
		    return 'm';
            } elseif($this->PreferredUnit->isMILES()) {
		    return 'ft';
	    }
	}    
        
	/*
	 * Unit for elevation
	 * @return string
	 */
	public function unitForElevation() {
	    if($this->PreferredUnit->isKM()) {
		    return 'hm';
            } elseif($this->PreferredUnit->isMILES()) {
		    return 'ft';
	    }
	} 
        
        /* TODO factor(), factorForShortDistance(), factorForElevation() */
        
	/**
	 * Format distance as string
	 * @param mixed $format [optional] set as true for display as meter, can be 'auto'
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public function string($format = false, $decimals = false, $withUnit = true) {
		if ($format == self::FORMAT_AUTO) {
			if ($this->Distance <= 1.0 || $this->Distance == 1.5 || $this->Distance == 3.0) {
				$format = true;
			} else {
				$format = false;
			}
		}

		if ($format === true) {
		    if($this->PreferredUnit->isKM())
			return $this->stringMeter($withUnit);
		    elseif($this->PreferredUnit->isMILES())
			return $this->stringYards($withUnit);
		} else {
		    if($this->PreferredUnit->isKM())
			return $this->stringKilometer($decimals, $withUnit);
		    elseif($this->PreferredUnit->isMILES())
			return $this->stringMiles($decimals, $withUnit);
		    
		}
	}
        
	/**
	 * Format elevation as string
	 * @param mixed $format [optional] set as true for display as meter, can be 'auto'
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public function stringForDistanceFeet($withUnit = true) {
                if($this->PreferredUnit->isKM())
                    return $this->stringMeter($withUnit);
                elseif($this->PreferredUnit->isMILES())
                    return $this->stringFeet($withUnit);
		    
		
	}
        
	/**
	 * Format elevation as string
	 * @param mixed $format [optional] set as true for display as meter, can be 'auto'
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public function stringForDistanceYards($decimals = false, $withUnit = true) {
                if($this->PreferredUnit->isKM())
                    return $this->stringMeter($withUnit);
                elseif($this->PreferredUnit->isMILES())
                    return $this->stringYards($withUnit);
		    
		
	}

	/**
	 * String: as meter
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringMeter($withUnit = true) {
		return number_format($this->Distance*1000, 0, '', '.').($withUnit ? 'm' : '');
	}
        
	/**
	 * String: as feet
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringFeet($withUnit = true) {
		return number_format($this->Distance * self::FEET_MULTIPLIER, 0, '', '.').($withUnit ? '&nbsp;ft' : '');
	}

	/**
	 * String: as kilometer
	 * @param int $decimals [optional]
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringKilometer($decimals = false, $withUnit = true) {
		if ($decimals === false) {
			$decimals = self::$DEFAULT_DECIMALS;
		}

		return number_format($this->Distance, $decimals, self::$DECIMAL_POINT, self::$THOUSANDS_POINT).($withUnit ? '&nbsp;km' : '');
	}
        
	/**
	 * String: as yard
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringYards($withUnit = true) {
		return number_format($this->Distance * self::YARD_MULTIPLIER, 0, '', '.').($withUnit ? '&nbsp;y' : '');
	}
        
	/**
	 * String: as mile
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringMiles($decimals = false, $withUnit = true) {
		if ($decimals === false) {
			$decimals = self::$DEFAULT_DECIMALS;
		}

		return number_format($this->Distance * self::MILE_MULTIPLIER, $decimals, self::$DECIMAL_POINT, self::$THOUSANDS_POINT).($withUnit ? '&nbsp;mi' : '');
	}


	/**
	 * Multiply distance
	 * @param float $factor
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function multiply($factor) {
		$this->Distance *= $factor;

		return $this;
	}

	/**
	 * Add another distance
	 * @param \Runalyze\Activity\Distance $object
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function add(Distance $object) {
		$this->Distance += $object->kilometer();

		return $this;
	}

	/**
	 * Subtract another distance
	 * @param \Runalyze\Activity\Distance $object
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function subtract(Distance $object) {
		$this->Distance -= $object->kilometer();

		return $this;
	}

	/**
	 * Is distance negative?
	 * @return boolean
	 */
	public function isNegative() {
		return ($this->Distance < 0);
	}

	/**
	 * Is distance zero?
	 * @return boolean
	 */
	public function isZero() {
		return ($this->Distance == 0);
	}
        
}