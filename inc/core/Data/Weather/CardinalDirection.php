<?php
/**
 * This file contains class::Temperature
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

class CardinalDirection {

	/**
	 * Direction in degree
	 * @var float
	 */
	protected $value;
    
	/**
	 * Construct wind degree object
	 * @param float $value
	 * @param int $unit
	 */
	public function __construct($value = null) {
		$this->setDegree($value);
	}
	
	/**
	 * Set degree
	 * @param float $value
	 * @param int $unit
	 */
	public function setDegree($value) {
			$this->value = $value;
	}
	
	/**
	 * Value
	 * @return null|int
	 */
	public function value() {
		return $this->getDirection($this->value);
	}

	/**
	 * As string
	 * @return string
	 */
	public function asString() {
		return $this->asStringWithoutUnit().'&nbsp;&deg;' ;
	}
	
    public static function getDirection($bearing)
    {
	echo $bearing;
	$cardinalDirections = array( 
	 __('N') => array(337.5, 22.5), 
	 __('NE') => array(22.5, 67.5), 
	 __('E') => array(67.5, 112.5), 
	 __('SE') => array(112.5, 157.5), 
	 __('S') => array(157.5, 202.5), 
	 __('SW') => array(202.5, 247.5), 
	 __('W') => array(247.5, 292.5), 
	 __('NW') => array(292.5, 337.5) 
	); 

	foreach ($cardinalDirections as $dir => $angles)
	{ 
	 if ($bearing >= $angles[0] && $bearing < $angles[1])
	 { 
	  $direction = $dir; 
	  break;
	 } 
	} 
	return $direction;
    }
}