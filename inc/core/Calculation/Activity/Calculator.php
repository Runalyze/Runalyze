<?php
/**
 * This file contains class::Calculator
 * @package Runalyze\Calculation\Activity
 */

namespace Runalyze\Calculation\Activity;

use Runalyze\Calculation\JD;
use Runalyze\Calculation\Elevation;
use Runalyze\Calculation\Trimp;
use Runalyze\Context;
use Runalyze\Configuration;
use Runalyze\Mathematics\Distribution\TimeSeries;
use Runalyze\Model;

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
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $Activity;

	/**
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Trackdata;

	/**
	 * @var \Runalyze\Model\Route\Entity
	 */
	protected $Route;

	/**
	 * Calculator for activity properties
	 * @param \Runalyze\Model\Activity\Entity $activity
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param \Runalyze\Model\Route\Entity $route
	 */
	public function __construct(
		Model\Activity\Entity $activity,
		Model\Trackdata\Entity $trackdata = null,
		Model\Route\Entity $route = null
	) {
		$this->Activity = $activity;
		$this->Trackdata = $trackdata;
		$this->Route = $route;
	}

	/**
	 * @return boolean
	 */
	protected function knowsTrackdata() {
		return (null !== $this->Trackdata);
	}

	/**
	 * @return boolean
	 */
	protected function knowsRoute() {
		return (null !== $this->Route);
	}

	/**
	 * @return float [ml/kg/min]
	 */
	public function estimateVO2maxByTime() {
		$VO2max = new JD\LegacyEffectiveVO2max;
        $VO2max->fromPace($this->Activity->distance(), $this->Activity->duration());

		return $VO2max->uncorrectedValue();
	}

	/**
	 * @param float|null $distance [km]
	 * @return float [ml/kg/min]
	 */
	public function estimateVO2maxByHeartRate($distance = null) {
		if (null === $distance) {
			$distance = $this->Activity->distance();
		}

        $VO2max = new JD\LegacyEffectiveVO2max;
        $VO2max->fromPaceAndHR(
			$distance,
			$this->Activity->duration(),
			$this->Activity->hrAvg()/Configuration::Data()->HRmax()
		);

		return $VO2max->value();
	}

	/**
	 * @return float [ml/kg/min]
	 */
	public function estimateVO2maxByHeartRateWithElevation() {
		if ($this->knowsRoute()) {
			if ($this->Route->elevationUp() > 0 || $this->Route->elevationDown() > 0) {
				return $this->estimateVO2maxByHeartRateWithElevationFor($this->Route->elevationUp(), $this->Route->elevationDown());
			}

			return $this->estimateVO2maxByHeartRateWithElevationFor($this->Route->elevation(), $this->Route->elevation());
		}

		return $this->estimateVO2maxByHeartRateWithElevationFor($this->Activity->elevation(), $this->Activity->elevation());
	}

	/**
	 * Calculate VDOT by heart rate with elevation influence
	 * @param int $up
	 * @param int $down
	 * @return float
	 */
	public function estimateVO2maxByHeartRateWithElevationFor($up, $down) {
		$Modifier = new Elevation\DistanceModifier(
			$this->Activity->distance(),
			$up,
			$down,
			Configuration::VO2max()
		);

		return $this->estimateVO2maxByHeartRate($Modifier->correctedDistance());
	}

	/**
	 * Calculate trimp
	 * @return int
	 */
	public function calculateTrimp() {
		if ($this->knowsTrackdata() && $this->Trackdata->has(Model\Trackdata\Entity::HEARTRATE)) {
			$Collector = new Trimp\DataCollector($this->Trackdata->heartRate(), $this->Trackdata->time());
			$data = $Collector->result();
		} elseif ($this->Activity->hrAvg() > 0) {
			$data = array($this->Activity->hrAvg() => $this->Activity->duration());
		} else {
			$Factory = Context::Factory();

			if ($this->Activity->typeid() > 0) {
				$data = array($Factory->type($this->Activity->typeid())->hrAvg() => $this->Activity->duration());
			} else {
				$data = array($Factory->sport($this->Activity->sportid())->avgHR() => $this->Activity->duration());
			}
		}

		$Athlete = Context::Athlete();
		$Calculator = new Trimp\Calculator($Athlete, $data);

		return round($Calculator->value());
	}
}
