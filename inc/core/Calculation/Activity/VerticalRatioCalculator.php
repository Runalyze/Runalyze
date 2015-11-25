<?php
/**
 * This file contains class::VerticalRatioCalculator
 * @package Runalyze\Calculation\Activity
 */

namespace Runalyze\Calculation\Activity;

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
			!$this->Trackdata->has(Trackdata\Object::STRIDE_LENGTH)
		) {
			return;
		}

		$Oscillation = $this->Trackdata->verticalOscillation();
		$StrideLength = $this->Trackdata->strideLength();
		$Size = $this->Trackdata->num();

		$this->VerticalRatio = array();

		for ($i = 0; $i < $Size; ++$i) {
			$this->VerticalRatio[] = ($StrideLength[$i] > 0) ? round(100 * $Oscillation[$i] / $StrideLength[$i], 1) : 0;
		}

		return $this->VerticalRatio;
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

		if (!$this->Trackdata->has(Trackdata\Object::TIME)) {
			return round(array_sum($this->VerticalRatio) / $this->Trackdata->num(), 1);
		}

		$Series = new TimeSeries($this->VerticalRatio, $this->Trackdata->time());
		$Series->calculateStatistic();

		return round($Series->mean(), 1);
	}
	
	/**
	 * Calculate vertical ratio for activity
	 * Use this method if trackdata is not available
	 * @param \Runalyze\Model\Activity\Object $activity
	 * @return int [%]
	 */
	public static function forActivity(Activity\Object $activity) {
		if ($activity->verticalOscillation() > 0 && $activity->strideLength() > 0) {
			return round(100 * $activity->verticalOscillation() / $activity->strideLength(), 1);
		}

		return 0;
	}
}