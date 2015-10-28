<?php
/**
 * This file contains class::Loop
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;

/**
 * Loop through trackdata object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Trackdata
 */
class Loop extends \Runalyze\Model\Loop {
	/**
	 * Object
	 * @var \Runalyze\Model\Trackdata\Object
	 */
	protected $Object;

	/**
	 * Construct
	 * @param \Runalyze\Model\Trackdata\Object $object
	 */
	public function __construct(Object $object) {
		parent::__construct($object);
	}

	/**
	 * Current time
	 * @return int
	 */
	public function time() {
		return $this->current(Object::TIME);
	}

	/**
	 * Current distance
	 * @return float
	 */
	public function distance() {
		return $this->current(Object::DISTANCE);
	}

	/**
	 * Move Distance
	 * @return bool
	 */
	public function nextDistance() {
		if (Configuration::General()->distanceUnitSystem()->isImperial()) {
			return $this->nextMile();
		}

		return $this->nextKilometer();
	}

	/**
	 * Next kilometer
	 * 
	 * Alias for <code>moveDistance(1.0)</code>
	 * @return boolean
	 */
	public function nextKilometer() {
		$this->moveDistance(1.0);

		return $this->isAtEnd();
	}
        
	/**
	 * Next mile
	 * 
	 * Alias for <code>moveDistance(1.60934)</code>
	 * @return boolean
	 */
	public function nextMile() {
		$this->moveDistance(1.60934);

		return $this->isAtEnd();
	}

	/**
	 * Move for time
	 * @param int $seconds
	 * @throws \RuntimeException for negative values or if time is empty
	 */
	public function moveTime($seconds) {
		$this->move(Object::TIME, $seconds);
	}

	/**
	 * Move to time
	 * @param int $seconds
	 * @throws \RuntimeException for negative values or if time is empty
	 */
	public function moveToTime($seconds) {
		$this->moveTo(Object::TIME, $seconds);
	}

	/**
	 * Move for distance
	 * @param float $kilometer
	 * @throws \RuntimeException for negative values or if distance is empty
	 */
	public function moveDistance($kilometer) {
		$this->move(Object::DISTANCE, $kilometer);
	}

	/**
	 * Move to distance
	 * @param float $kilometer
	 * @throws \RuntimeException for negative values or if distance is empty
	 */
	public function moveToDistance($kilometer) {
		$this->moveTo(Object::DISTANCE, $kilometer);
	}

	/**
	 * @param array $data
	 * @return \Runalyze\Model\Trackdata\Object
	 */
	protected function createNewObject(array $data) {
		return new Object($data);
	}
}