<?php
/**
 * This file contains class::Calculator
 * @package Runalyze\Calculation\Activity
 */

namespace Runalyze\Calculation\Activity;

use Runalyze\Model;
use Runalyze\Calculation\JD;
use Runalyze\Calculation\Elevation;
use Runalyze\Calculation\Trimp;
use Runalyze\Calculation\Distribution\TimeSeries;
use Runalyze\Context;
use Runalyze\Configuration;

/**
 * Calculate properties of activity object
 * 
 * This calculator will compute values as VDOT, TRIMP, etc.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Activity
 */
class Calculator {
	/**
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $Activity;

	/**
	 * @var \Runalyze\Model\Trackdata\Object
	 */
	protected $Trackdata;

	/**
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $Route;

	/**
	 * Calculator for activity properties
	 * @param \Runalyze\Model\Activity\Object $activity
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 * @param \Runalyze\Model\Route\Object $route
	 */
	public function __construct(
		Model\Activity\Object $activity,
		Model\Trackdata\Object $trackdata = null,
		Model\Route\Object $route = null
	) {
		$this->Activity = $activity;
		$this->Trackdata = $trackdata;
		$this->Route = $route;
	}

	/**
	 * @return boolean
	 */
	protected function knowsTrackdata() {
		return (NULL !== $this->Trackdata);
	}

	/**
	 * @return boolean
	 */
	protected function knowsRoute() {
		return (NULL !== $this->Route);
	}

	/**
	 * Calculate VDOT by time
	 * @return float
	 */
	public function calculateVDOTbyTime() {
		$VDOT = new JD\VDOT;
		$VDOT->fromPace($this->Activity->distance(), $this->Activity->duration());

		return $VDOT->uncorrectedValue();
	}

	/**
	 * Calculate VDOT by heart rate
	 * @param float $distance [optional]
	 * @return float
	 */
	public function calculateVDOTbyHeartRate($distance = null) {
		if (is_null($distance)) {
			$distance = $this->Activity->distance();
		}

		$VDOT = new JD\VDOT;
		$VDOT->fromPaceAndHR(
			$distance,
			$this->Activity->duration(),
			$this->Activity->hrAvg()/Configuration::Data()->HRmax()
		);

		return $VDOT->value();
	}

	/**
	 * Calculate VDOT by heart rate with elevation influence
	 * @return float
	 */
	public function calculateVDOTbyHeartRateWithElevation() {
		if ($this->knowsRoute()) {
			if ($this->Route->elevationUp() > 0 || $this->Route->elevationDown() > 0) {
				return $this->calculateVDOTbyHeartRateWithElevationFor($this->Route->elevationUp(), $this->Route->elevationDown());
			}

			return $this->calculateVDOTbyHeartRateWithElevationFor($this->Route->elevation(), $this->Route->elevation());
		}

		return $this->calculateVDOTbyHeartRateWithElevationFor($this->Activity->elevation(), $this->Activity->elevation());
	}

	/**
	 * Calculate VDOT by heart rate with elevation influence
	 * @param int $up
	 * @param int $down
	 * @return float
	 */
	public function calculateVDOTbyHeartRateWithElevationFor($up, $down) {
		$Modifier = new Elevation\DistanceModifier(
			$this->Activity->distance(),
			$up, 
			$down,
			Configuration::Vdot()
		);

		return $this->calculateVDOTbyHeartRate($Modifier->correctedDistance());
	}

	/**
	 * Calculate JD intensity
	 * @return int
	 */
	public function calculateJDintensity() {
		JD\Intensity::setVDOTshape(Configuration::Data()->vdot());
		JD\Intensity::setHRmax(Configuration::Data()->HRmax());

		$Intensity = new JD\Intensity();

		if ($this->knowsTrackdata() && $this->Trackdata->has(Model\Trackdata\Object::HEARTRATE) && $this->Trackdata->has(Model\Trackdata\Object::TIME)) {
			return $Intensity->calculateByHeartrate(
				new TimeSeries(
					$this->Trackdata->heartRate(),
					$this->Trackdata->time()
				)
			);
		} elseif ($this->Activity->hrAvg() > 0) {
			return $Intensity->calculateByHeartrateAverage($this->Activity->hrAvg(), $this->Activity->duration());
		} else {
			return $Intensity->calculateByPace($this->Activity->distance(), $this->Activity->duration());
		}
	}

	/**
	 * Calculate trimp
	 * @return int
	 */
	public function calculateTrimp() {
		if ($this->knowsTrackdata() && $this->Trackdata->has(Model\Trackdata\Object::HEARTRATE)) {
			$Collector = new Trimp\DataCollector($this->Trackdata->heartRate(), $this->Trackdata->time());
			$data = $Collector->result();
		} elseif ($this->Activity->hrAvg() > 0) {
			$data = array($this->Activity->hrAvg() => $this->Activity->duration());
		} else {
			$Factory =Context::Factory();
			$data = array($Factory->sport($this->Activity->sportid())->avgHR() => $this->Activity->duration());
		}

		$Athlete = Context::Athlete();
		$Calculator = new Trimp\Calculator($Athlete, $data);

		return round($Calculator->value());
	}
}