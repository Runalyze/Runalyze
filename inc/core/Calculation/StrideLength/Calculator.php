<?php
/**
 * This file contains class::Calculator
 * @package Runalyze\Calculation\StrideLength
 */

namespace Runalyze\Calculation\StrideLength;

use Runalyze\Model\Activity;
use Runalyze\Model\Trackdata;
use Runalyze\Calculation\Distribution\TimeSeries;

/**
 * Calculate stride length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\StrideLength
 */
class Calculator {
	/**
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Trackdata;

	/**
	 * @var array
	 */
	protected $Strides = array();

	/**
	 * Calculator for stride length
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 */
	public function __construct(Trackdata\Entity $trackdata) {
		$this->Trackdata = $trackdata;
	}

	/**
	 * Calculate strides array
	 * @return array
	 */
	public function calculate() {
		if (
			!$this->Trackdata->has(Trackdata\Entity::TIME) ||
			!$this->Trackdata->has(Trackdata\Entity::DISTANCE) ||
			!$this->Trackdata->has(Trackdata\Entity::CADENCE)
		) {
			return [];
		}

		$Time = $this->Trackdata->time();
		$Distance = $this->Trackdata->distance();
		$Cadence = $this->Trackdata->cadence();
		$Size = $this->Trackdata->num();

		$this->Strides[] = ($Cadence[0] > 0 && $Time[0] > 0)
			? round( $Distance[0] * 1000 * 100 / ($Cadence[0] * 2 / 60 * $Time[0]) )
			: 0;

		for ($i = 1; $i < $Size; ++$i) {
			$this->Strides[] = ($Cadence[$i] > 0 && $Time[$i] - $Time[$i-1] > 0)
				? round( ($Distance[$i] - $Distance[$i-1]) * 1000 * 100 / ($Cadence[$i] * 2 / 60 * ($Time[$i] - $Time[$i-1])) )
				: 0;
		}

		return $this->Strides;
	}

	/**
	 * @return array
	 */
	public function stridesData() {
		return $this->Strides;
	}

	/**
	 * Calculate average stride length
	 * @return int [cm]
	 */
	public function average() {
		if (empty($this->Strides)) {
			return 0;
		}

		$Series = new TimeSeries($this->Strides, $this->Trackdata->time());
		$Series->calculateStatistic();

		return round($Series->mean());
	}

	/**
	 * Calculate stride length for activity
	 * Use this method if trackdata is not available
	 * @param \Runalyze\Model\Activity\Entity $activity
	 * @return int [cm]
	 */
	public static function forActivity(Activity\Entity $activity) {
		if ($activity->cadence() > 0 && $activity->duration() > 0) {
			return round($activity->distance() * 1000 * 100 / ($activity->cadence() * 2 / 60 * $activity->duration()));
		}

		return 0;
	}
}