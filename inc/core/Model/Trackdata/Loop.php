<?php
/**
 * This file contains class::Loop
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;

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
	 * @return int
	 */
	public function distance() {
		return $this->current(Object::DISTANCE);
	}

	/**
	 * Next kilometer
	 * 
	 * Alias for <code>moveDistance(1.0)</code>
	 * @return boolean
	 */
	public function nextKilometer() {
		return $this->moveDistance(1.0);
	}

	/**
	 * Move for time
	 * @param int $seconds
	 * @throws \RuntimeException
	 */
	public function moveTime($seconds) {
		$this->move(Object::TIME, $seconds);
	}

	/**
	 * Move to time
	 * @param int $seconds
	 * @throws \RuntimeException
	 */
	public function moveToTime($seconds) {
		$this->moveTo(Object::TIME, $seconds);
	}

	/**
	 * Move for distance
	 * @param float $kilometer
	 * @throws \RuntimeException
	 */
	public function moveDistance($kilometer) {
		$this->move(Object::DISTANCE, $kilometer);
	}

	/**
	 * Move to distance
	 * @param float $kilometer
	 * @throws \RuntimeException
	 */
	public function moveToDistance($kilometer) {
		$this->moveTo(Object::DISTANCE, $kilometer);
	}
}