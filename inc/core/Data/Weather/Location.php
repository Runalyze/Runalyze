<?php
/**
 * This file contains class::Location
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;
use League\Geotools\Geohash\Geohash;
use League\Geotools\Coordinate\Coordinate;
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
	 * Set geohash
	 * @param string $geohash
	 */
	public function setGeohash($geohash) {
		$decoded = (new Geohash)->decode($geohash)->getCoordinate();
		$this->Latitude = $decoded->getLatitude();
		$this->Longitude = $decoded->getLongitude();
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
	 * Geohash
	 * @return string
	 */
	public function geohash() {
	    if ($this->hasPosition()) {
			return (new Geohash)->encode(new Coordinate(array((float)$this->lat(), (float)$this->lon())), 12)->getGeohash();
	    }
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
	 * @param int $seconds
	 * @return bool true if the timestamp is older than 24 hours
	 */
	public function isOlderThan($seconds =  DAY_IN_S) {
		return $this->hasTimestamp() && ($this->Timestamp < time() - $seconds);
	}
}