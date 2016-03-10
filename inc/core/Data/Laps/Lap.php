<?php
/**
 * This file contains class::Lap
 * @package Runalyze\Data\Laps
 */

namespace Runalyze\Data\Laps;

use Runalyze\Activity\Duration;
use Runalyze\Activity\Distance;
use Runalyze\Activity\HeartRate;
use Runalyze\Activity\Pace;
use Runalyze\Context;

/**
 * A single lap
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Laps
 */
class Lap {
	/**
	 * @var int enum
	 */
	const MODE_ACTIVE = 0;

	/**
	 * @var int enum
	 */
	const MODE_RESTING = 1;

	/**
	 * @var \Runalyze\Activity\Duration 
	 */
	protected $LapDuration;

	/**
	 * @var \Runalyze\Activity\Distance 
	 */
	protected $LapDistance;

	/**
	 * @var \Runalyze\Activity\Pace
	 */
	protected $LapPace = null;

	/**
	 * @var int enum
	 */
	protected $Mode;

	/**
	 * @var \Runalyze\Activity\Duration 
	 */
	protected $TrackDurationAtEnd = null;

	/**
	 * @var \Runalyze\Activity\Distance 
	 */
	protected $TrackDistanceAtEnd = null;

	/**
	 * @var \Runalyze\Activity\HeartRate
	 */
	protected $HRavg = null;

	/**
	 * @var \Runalyze\Activity\HeartRate
	 */
	protected $HRmax = null;

	/**
	 * @var int [m]
	 */
	protected $ElevationUp = false;

	/**
	 * @var int [m]
	 */
	protected $ElevationDown = false;

	/**
	 * @var array
	 */
	protected $AdditionalValues = array();

	/**
	 * @var \Runalyze\Athlete
	 */
	protected $Athlete;

	/**
	 * @param int $duration [optional]
	 * @param float $distance [optional]
	 * @param int $mode [optional]
	 */
	public function __construct($duration = 0, $distance = 0.0, $mode = self::MODE_ACTIVE) {
		$this->setDuration($duration);
		$this->setDistance($distance);
		$this->setMode($mode);

		$this->Athlete = Context::Athlete();
	}

	/**
	 * @param int $duration [s]
	 */
	public function setDuration($duration) {
		$this->LapDuration = new Duration($duration);
	}

	/**
	 * @param float $distance [km]
	 */
	public function setDistance($distance) {
		$this->LapDistance = new Distance($distance);
	}

	/**
	 * @param int $mode
	 */
	public function setMode($mode) {
		$this->Mode = $mode;
	}

	/**
	 * @param int $duration [s]
	 */
	public function setTrackDuration($duration) {
		$this->TrackDurationAtEnd = new Duration($duration);
	}

	/**
	 * @param float $distance [km]
	 */
	public function setTrackDistance($distance) {
		$this->TrackDistanceAtEnd = new Distance($distance);
	}

	/**
	 * @var int $avg
	 * @var int $max
	 */
	public function setHR($avg, $max) {
		$this->HRavg = new HeartRate($avg, $this->Athlete);
		$this->HRmax = new HeartRate($max, $this->Athlete);
	}

	/**
	 * @param int $up [m]
	 * @param int $down [m]
	 */
	public function setElevation($up, $down) {
		$this->ElevationUp = $up;
		$this->ElevationDown = $down;
	}

	/**
	 * @return \Runalyze\Activity\Duration
	 */
	public function duration() {
		return $this->LapDuration;
	}

	/**
	 * @return \Runalyze\Activity\Distance
	 */
	public function distance() {
		return $this->LapDistance;
	}

	/**
	 * @return \Runalyze\Activity\Pace
	 */
	public function pace() {
		if (null == $this->LapPace) {
			$this->LapPace = new Pace($this->LapDuration->seconds(), $this->LapDistance->kilometer());
		}

		return $this->LapPace;
	}

	/**
	 * @return \Runalyze\Activity\Duration
	 */
	public function trackDuration() {
		return $this->TrackDurationAtEnd;
	}

	/**
	 * @return \Runalyze\Activity\Distance
	 */
	public function trackDistance() {
		return $this->TrackDistanceAtEnd;
	}

	/**
	 * @return boolean
	 */
	public function hasTrackValues() {
		return (null !== $this->TrackDurationAtEnd);
	}

	/**
	 * @return boolean
	 */
	public function isActive() {
		return (self::MODE_ACTIVE == $this->Mode);
	}

	/**
	 * @return boolean
	 */
	public function hasHR() {
		return (null !== $this->HRavg);
	}

	/**
	 * @return \Runalyze\Activity\HeartRate
	 */
	public function HRavg() {
		return $this->HRavg;
	}

	/**
	 * @return \Runalyze\Activity\HeartRate
	 */
	public function HRmax() {
		return $this->HRmax;
	}

	/**
	 * @return boolean
	 */
	public function hasElevation() {
		return (false !== $this->ElevationUp);
	}

	/**
	 * @return int [m]
	 */
	public function elevationUp() {
		return $this->ElevationUp;
	}

	/**
	 * @return int [m]
	 */
	public function elevationDown() {
		return $this->ElevationDown;
	}

	/**
	 * @param array $data
	 */
	public function setAdditionalValues(array $data) {
		$this->AdditionalValues = $data;
	}

	/**
	 * @return array
	 */
	public function additionalValues() {
		return $this->AdditionalValues;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setAdditionalValue($key, $value) {
		$this->AdditionalValues[$key] = $value;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function hasAdditionalValue($key) {
		return isset($this->AdditionalValues[$key]);
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function additionalValue($key) {
		return $this->AdditionalValues[$key];
	}
}