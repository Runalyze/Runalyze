<?php
/**
 * This file contains class::Loop
 * @package Runalyze\Model\Swim
 */

namespace Runalyze\Model\Swim;

/**
 * Loop through swimdata object
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Swim
 */
class Loop extends \Runalyze\Model\Loop {
	/**
	 * Object
	 * @var \Runalyze\Model\Swim\Object
	 */
	protected $Object;

	/**
	 * Construct
	 * @param \Runalyze\Model\Swim\Object $object
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


}