<?php
/**
 * This file contains class::Loop
 * @package Runalyze\Model\Swimdata
 */

namespace Runalyze\Model\Swimdata;

/**
 * Loop through swimdata object
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Swimdata
 */
class Loop extends \Runalyze\Model\Loop {
	/**
	 * Object
	 * @var \Runalyze\Model\Swimdata\Object
	 */
	protected $Object;

	/**
	 * Construct
	 * @param \Runalyze\Model\Swimdata\Object $object
	 */
	public function __construct(Object $object) {
		parent::__construct($object);
	}

	/**
	 * Current time
	 * @return int
	 */
	public function swimtime() {
		return $this->current(Object::SWIMTIME);
	}


	/**
	 * Move for time
	 * @param int $seconds
	 * @throws \RuntimeException for negative values or if time is empty
	 */
	public function moveTime($seconds) {
		$this->move(Object::SWIMTIME, $seconds);
	}

	/**
	 * Move to time
	 * @param int $seconds
	 * @throws \RuntimeException for negative values or if time is empty
	 */
	public function moveToTime($seconds) {
		$this->moveTo(Object::SWIMTIME, $seconds);
	}


}