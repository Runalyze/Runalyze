<?php
/**
 * This file contains class::WindChillFactor
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;
use Runalyze\Activity\Temperature as ActivityTemperature;
use Runalyze\Activity\Pace;
/**
 * WindChillFactor
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Data\Weather
 */
class WindChillFactor {
    
	/**
	 * WindChillFactor
	 * @object \Runalyze\Data\Weather\Temerature
	 */
	protected $WindChillFactor;
	
	/**
	 * Construct WindChillFactor
	 * @param \Runalyze\Data\Weather\WindSpeed $windSpeed
	 * @param \Runalyze\Activity\Temerature $temperature
	 * @param \Runalyze\Activity\Pace $activitySpeed
	 */
	public function __construct(WindSpeed $windSpeed, ActivityTemperature $temperature, Pace $activitySpeed) {
		$this->set($windSpeed, $temperature, $activitySpeed);
	}
    
	/**
	 * Calculate and set WindChillEffect
	 * @param \Runalyze\Data\Weather\WindSpeed $windSpeed
	 * @param \Runalyze\Activity\Temperature $temperature
	 * @param \Runalyze\Activity\Pace $activitySpeed 
	 * @param int $activitySpeed
	 */
	public function set(WindSpeed $windSpeed, ActivityTemperature $temperature, Pace $activitySpeed) {
		$kmh = $windSpeed->value() + $activitySpeed->asKmPerHour();
		$calc = 13.12 + 0.6215 * $temperature->value() - 11.37 * pow($kmh,0.16) + 0.3965 * $temperature->value() * pow($kmh,0.16);
		$this->WindChillFactor = new ActivityTemperature($calc);
	}

	/**
	 * Label for value
	 * @return string
	 */
	public function label() {
	    return __('Wind chill factor');
	}
	
	
	/**
	 * Label for value
	 * @return string
	 */
	public function unit() {
	    	    return $this->WindChillFactor->unit();
	}
	
	/**
	 * Value
	 * @return null|int
	 */
	public function value() {
		return $this->WindChillFactor->value();
	}
	
	/**
	 * Value in preffered unit
	 */
	public function valueInPreferredUnit() {
	    return $this->WindChillFactor->valueInPreferredUnit();
	}
	
	/**
	 * WindChillFactor is unknown?
	 * @return bool
	 */
	public function isUnknown() {
		return is_null($this->WindChillFactor);
	}
}