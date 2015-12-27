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
	 * @var \Runalyze\Model\Route\Entity
	 */
	protected $Object;

	/**
	 * Construct
	 * @param \Runalyze\Model\Route\Entity $object
	 */
	public function __construct(Entity $object) {
		parent::__construct($object);
	}

	/**
	 * Current latitude
	 * @return float
	 */
	public function latitude() {
		return (new Geohash())->decode($this->current(Entity::GEOHASHES))->getCoordinate()->getLatitude();
	}

	/**
	 * Current longitude
	 * @return float
	 */
	public function longitude() {
		return (new Geohash())->decode($this->current(Entity::GEOHASHES))->getCoordinate()->getLongitude();
	}
	
	/**
	 * Current geohash
	 * @return string
	 */
	public function geohash() {
		return $this->current(Entity::GEOHASHES);
	}

	/**
	 * @return \League\Geotools\Coordinate\CoordinateInterface
	 */
	public function coordinate() {
		return (new Geohash())->decode($this->current(Entity::GEOHASHES))->getCoordinate();
	}

	/**
	 * Calculate distance of current step from latitude/longitude
	 * @return double
	 */
	public function calculatedStepDistance() {
	    $LastGeohash = (new Geohash())->decode($this->Object->at($this->LastIndex, Entity::GEOHASHES))->getCoordinate();
	    $IndexGeohash = (new Geohash())->decode($this->Object->at($this->Index, Entity::GEOHASHES))->getCoordinate();

	    return Entity::gpsDistance(
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
			return $this->slice(Entity::ELEVATIONS_CORRECTED);
		} elseif ($this->Object->hasOriginalElevations()) {
			return $this->slice(Entity::ELEVATIONS_ORIGINAL);
		}

		return array();
	}

	/**
	 * @param array $data
	 * @return \Runalyze\Model\Route\Entity
	 */
	protected function createNewObject(array $data) {
		return new Entity($data);
	}
}