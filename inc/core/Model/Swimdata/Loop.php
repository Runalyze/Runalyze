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
	 * Current stroke
	 * @return int
	 */
	public function stroke() {
		return $this->current(Object::STROKE);
	}
        
	/**
	 * Current stroke
	 * @return int
	 */
	public function stroketype() {
		return $this->current(Object::STROKETYPE);
	}

	/**
	 * Current swolf
	 * @return int
	 */
	public function swolf() {
		return $this->current(Object::SWOLF);
	}

	/**
	 * @param array $data
	 * @return \Runalyze\Model\Swimdata\Object
	 */
	protected function createNewObject(array $data) {
		return new Object($data);
	}
}