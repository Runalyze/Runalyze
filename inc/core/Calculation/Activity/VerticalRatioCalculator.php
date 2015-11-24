<?php
/**
 * This file contains class::VerticalRatioCalculator
 * @package Runalyze\Calculation\Activity
 */

namespace Runalyze\Calculation\Activity;

use Runalyze\Model\Trackdata;
use Runalyze\Model\Activity;
use Runalyze\Model\Trackdata;
use Runalyze\Calculation\Distribution\TimeSeries;


/**
 * Vertical Ratio Calculator
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Calculation\Activity
 */
class VerticalRatioCalculator {
    
    	/**
	 * @var \Runalyze\Model\Trackdata\Object
	 */
	protected $Trackdata;

	/**
	 * @var array
	 */
	protected $VerticalRatio = array();

	/**
	 * Calculator for stride length
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 */
	public function __construct(Trackdata\Object $trackdata) {
		$this->Trackdata = $trackdata;
	}
	
	/**
	 * Calculate vertical ratio array
	 * @return array
	 */
	public function calculate() {
		if (
			!$this->Trackdata->has(Trackdata\Object::VERTICAL_OSCILLATION) ||
			!$this->Trackdata->has(Trackdata\Object::DISTANCE) ||
			!$this->Trackdata->has(Trackdata\Object::CADENCE)
		) {
			return;
		}
		//
	}

	
	/**
	 * @return array
	*/
	public function verticalRatioData() {
	    return $this->VerticalRatio;
	}
	
	/**
	 * Calculate average Vertical Ratio
	 * @return int [%]
	 */
	public function average() {
		if (empty($this->VerticalRatio)) {
			return 0;
		}

		$Series = new TimeSeries($this->VerticalRatio, $this->Trackdata->time());
		$Series->calculateStatistic();

		return round($Series->mean());
	}
	
	/**
	 * Calculate stride length for activity
	 * Use this method if trackdata is not available
	 * @param \Runalyze\Model\Activity\Object $activity
	 * @return int [cm]
	 */
	public static function forActivity(Activity\Object $activity) {
		if ($activity->verticalOscillation() > 0 && $activity->strideLength() > 0) {
			return round(($activity->verticalOscillation() * 100 ) / $activity->strideLength());
		}

		return 0;
	}
}