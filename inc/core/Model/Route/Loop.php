<?php
/**
 * This file contains class::Loop
 * @package Runalyze\Model\Route
 */

namespace Runalyze\Model\Route;
use League\Geotools\Geohash\Geohash;

/**
 * Loop through route object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Route
 */
class Loop extends \Runalyze\Model\Loop {
	/**
	 * Object
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $Object;

	/**
	 * Construct
	 * @param \Runalyze\Model\Route\Object $object
	 */
	public function __construct(Object $object) {
		parent::__construct($object);
	}

	/**
	 * Current latitude
	 * @return float
	 */
	public function latitude() {
		return (new Geohash())->decode($this->current(Object::GEOHASHES))->getCoordinate()->getLatitude();
	}

	/**
	 * Current longitude
	 * @return float
	 */
	public function longitude() {
		return (new Geohash())->decode($this->current(Object::GEOHASHES))->getCoordinate()->getLongitude();
	}
	
	/**
	 * Current geohash
	 * @return string
	 */
	public function geohash() {
		return $this->current(Object::GEOHASHES);
	}

	/**
	 * Calculate distance of current step from latitude/longitude
	 * @return double
	 */
	public function calculatedStepDistance() {
	    $LastGeohash = (new Geohash())->decode($this->Object->at($this->LastIndex, Object::GEOHASHES))->getCoordinate();
	    $IndexGeohash = (new Geohash())->decode($this->Object->at($this->Index, Object::GEOHASHES))->getCoordinate();

	    return Object::gpsDistance(
			$LastGeohash->getLatitude(),
			$LastGeohash->getLongitude(),
			$IndexGeohash->getLatitude(),
			$IndexGeohash->getLongitude()
		);
	}

	/**
	 * @return array
	 */
	public function sliceElevation() {
		if ($this->Object->hasCorrectedElevations()) {
			return $this->slice(Object::ELEVATIONS_CORRECTED);
		} elseif ($this->Object->hasOriginalElevations()) {
			return $this->slice(Object::ELEVATIONS_ORIGINAL);
		}

		return array();
	}

	/**
	 * @param array $data
	 * @return \Runalyze\Model\Route\Object
	 */
	protected function createNewObject(array $data) {
		return new Object($data);
	}
}