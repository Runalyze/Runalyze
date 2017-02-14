<?php

namespace Runalyze\Calculation\Activity;

use Runalyze\Mathematics\Distribution\TimeSeries;
use Runalyze\Model\Activity;
use Runalyze\Model\Trackdata;

/**
 * Vertical Ratio Calculator
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Calculation\Activity
 */
class VerticalRatioCalculator {
    /**
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Trackdata;

	/**
	 * @var array
	 */
	protected $VerticalRatio = array();

	/**
	 * Calculator for stride length
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 */
	public function __construct(Trackdata\Entity $trackdata) {
		$this->Trackdata = $trackdata;
	}

	/**
	 * Calculate vertical ratio array
	 * @return array [%o]
	 */
	public function calculate() {
		if (
			!$this->Trackdata->has(Trackdata\Entity::VERTICAL_OSCILLATION) ||
			!$this->Trackdata->has(Trackdata\Entity::STRIDE_LENGTH)
		) {
			return [];
		}

		$Oscillation = $this->Trackdata->verticalOscillation();
		$StrideLength = $this->Trackdata->strideLength();
		$Size = $this->Trackdata->num();

		$this->VerticalRatio = array();

		for ($i = 0; $i < $Size; ++$i) {
			$this->VerticalRatio[] = ($StrideLength[$i] > 0) ? round(100 * $Oscillation[$i] / $StrideLength[$i]) : 0;
		}

		return $this->VerticalRatio;
	}


	/**
	 * @return array [%o]
	*/
	public function verticalRatioData() {
	    return $this->VerticalRatio;
	}

	/**
	 * Calculate average Vertical Ratio
	 * @return null|int [%o]
	 */
	public function average() {
		if (empty($this->VerticalRatio)) {
			return null;
		}

		if (!$this->Trackdata->has(Trackdata\Entity::TIME)) {
			return round(array_sum($this->VerticalRatio) / $this->Trackdata->num());
		}

		$Series = new TimeSeries($this->VerticalRatio, $this->Trackdata->time());
		$Series->calculateStatistic();

		return round($Series->mean());
	}

	/**
	 * Calculate vertical ratio for activity
	 * Use this method if trackdata is not available
	 * @param \Runalyze\Model\Activity\Entity $activity
	 * @return null|int [%o]
	 */
	public static function forActivity(Activity\Entity $activity) {
		if ($activity->verticalOscillation() > 0 && $activity->strideLength() > 0) {
			return round(100 * $activity->verticalOscillation() / $activity->strideLength());
		}

		return null;
	}
}
