<?php
/**
 * This file contains class::BeautfortScala
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

use Runalyze\Activity\ValueInterface;

/**
 * Beautfort Scala
 * @author Michael Pohl
 * @package Runalyze\Data\Weather
 */

class BeautfortScala implements ValueInterface {
    
    protected $WindRange = array(
                            array(0,1), 
                            array(1,6),
                            array(6,12),
                            array(12,20),
                            array(20,29),
                            array(29,39),
                            array(39,50),
                            array(50,62),
                            array(62,75),
                            array(75,89),
                            array(89,103),
                            array(103,118),
                            array(118,1000)
            );
        
	/**
	 * Windspeed
	 * @var int in km/h
	 */
	protected $windSpeed;
        
	/**
	 * BTF
	 * @var int BTF
	 */
	protected $btf;

	/**
	 * Wind condition
	 * @param \Runalyze\Data\Weather\WindSpeed
	 */
	public function __construct(WindSpeed $windSpeed = null) {
	    if (!is_null($windSpeed)) {
		$this->setFromWindSpeed($windSpeed);
	    }
	}

        /**
         * Set internal btf value
         */
        public function setBtf() {
            foreach ($this->WindRange as $key => $range) {
                if ($this->windSpeed >= $range[0] && $this->windSpeed < $range[1]) {
                    $this->btf = $key;
                }
            }
        }
	
	/**
	 * Set String
	 * @param \Runalyze\Data\Weather\WindSpeed
	 * @return string
	 */
	public static function getString(WindSpeed $windSpeed) {
                return (new self($windSpeed))->string();
	}
	
	/**
	 * Set
	 * @param \Runalyze\Data\Weather\WindSpeed
	 * @return string
	 */
	public static function getShortString(WindSpeed $windSpeed) {
                return (new self($windSpeed))->shortString();
	}
	
	/**
	 * Get btf value
	 * @return int
	 */
	public function get() {
	    return $this->btf;
	}
	
	/**
	 * Set btf value
	 * @param $btf
	 */
	public function set($btf) {
	    $this->btf = $btf;
	}
	
	/**
	 * Set wind speed
	 * @param \Runalyze\Data\Weather\WindSpeed
	 */
	public function setFromWindSpeed(WindSpeed $windSpeed) {
	    $this->windSpeed = $windSpeed->inKilometerPerHour();
	    $this->setBtf();
	}

	/**
	 * String
	 * @return string
	 */
	public function string($withUnit = true) {
	    if ($withUnit) {
		return $this->shortString();
	    } else {
		return $this->btf;
	    }
	}

	/**
	 * String
	 * @return string
	 */
	public function longString() {
		switch ($this->btf) {
			case 0:
				return '0 btf ('.__('Calm').')';
			case 1:
				return '1 btf ('.__('Light air').')';
			case 2:
				return '2 btf ('.__('Light breeze').')';
			case 3:
				return '3 btf ('.__('Gentle breeze').')';
			case 4:
				return '4 btf ('.__('Moderate breeze').')';
			case 5:
				return '5 btf ('.__('Fresh breeze').')';
			case 6:
				return '6 btf ('.__('Strong breeze').')';
			case 7:
				return '7 btf ('.__('High wind').')';
			case 8:
				return '8 btf ('.__('Fresh gale').')';
			case 9:
				return '9 btf ('.__('Strong gale').')';
			case 10:
				return '10 btf ('.__('Whole gale').')';
			case 11:
				return '11 btf ('.__('Violent storm').')';
			case 12:
				return '12 btf ('.__('Hurricane force').')';
			default:
				return __('unknown');
		}
	}
        
        /**
	 * Short string
	 * @return string
	 */
	public function shortString() {
	    if ($this->btf >= 0 && $this->btf <=12) {
		return $this->btf.' btf';
	    } else {
		return '';
	    }
	}
	
	/**
	 * Label for value
	 * @return string
	 */
	public function label()
	{
	    return __('Beautfort Scala');
	}
	
	/**
	 * Label for value
	 * @return string
	 */
	public function unit()
	{
	    return 'btf';
	}

	/**
	 * Value
	 * @return null|int
	 */
	public function value()
	{
	    return $this->btf;
	}
}