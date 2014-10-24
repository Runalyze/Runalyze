<?php
/**
 * This file contains class::Location
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

/**
 * Weather location
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class Location {
	/**
	 * Latitude
	 * @var float
	 */
	protected $Latitude = null;

	/**
	 * Longitude
	 * @var float
	 */
	protected $Longitude = null;

	/**
	 * Timestamp
	 * @var int
	 */
	protected $Timestamp = null;

	/**
	 * Location name
	 * @var string
	 */
	protected $LocationName = '';

	/**
	 * Set position
	 * @param float $latitude
	 * @param float $longitude
	 */
	public function setPosition($latitude, $longitude) {
		$this->Latitude = $latitude;
		$this->Longitude = $longitude;
	}

	/**
	 * Set timestamp
	 * @param int $timestamp
	 */
	public function setTimestamp($timestamp) {
		$this->Timestamp = $timestamp;
	}

	/**
	 * Set location name
	 * @param string $location
	 */
	public function setLocationName($location) {
		$this->LocationName = $location;
	}

	/**
	 * Latitude
	 * @return float
	 */
	public function lat() {
		return $this->Latitude;
	}

	/**
	 * Longitude
	 * @return float
	 */
	public function lon() {
		return $this->Longitude;
	}

	/**
	 * Time
	 * @return int
	 */
	public function time() {
		return $this->Timestamp;
	}

	/**
	 * Location name
	 * @return string
	 */
	public function name() {
		return $this->LocationName;
	}

	/**
	 * Is position set?
	 * @return bool
	 */
	public function hasPosition() {
		return (
			!is_null($this->Latitude) &&
			!is_null($this->Longitude) &&
			($this->Latitude != 0 || $this->Longitude != 0)
		);
	}

	/**
	 * Has location name?
	 * @return string
	 */
	public function hasLocationName() {
		return (strlen($this->LocationName) > 0);
	}

	/**
	 * Is position set?
	 * @return bool
	 */
	public function hasTimestamp() {
		return !is_null($this->Timestamp) && $this->Timestamp != 0;
	}

	/**
	 * Is the location old?
	 * @return bool true if the timestamp is older than 24 hours
	 */
	public function isOld() {
		return $this->hasTimestamp() && ($this->Timestamp < time() - DAY_IN_S);
	}
}