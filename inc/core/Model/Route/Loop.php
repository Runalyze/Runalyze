<?php
/**
 * This file contains class::Loop
 * @package Runalyze\Model\Route
 */

namespace Runalyze\Model\Route;

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
		return $this->current(Object::LATITUDES);
	}

	/**
	 * Current longitude
	 * @return float
	 */
	public function longitude() {
		return $this->current(Object::LONGITUDES);
	}

	/**
	 * Calculate distance of current step from latitude/longitude
	 * @return double
	 */
	public function calculatedStepDistance() {
		return Object::gpsDistance(
			$this->Object->at($this->LastIndex, Object::LATITUDES),
			$this->Object->at($this->LastIndex, Object::LONGITUDES),
			$this->Object->at($this->Index, Object::LATITUDES),
			$this->Object->at($this->Index, Object::LONGITUDES)
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