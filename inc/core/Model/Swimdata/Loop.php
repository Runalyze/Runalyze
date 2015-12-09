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
	 * @var \Runalyze\Model\Swimdata\Entity
	 */
	protected $Object;

	/**
	 * Construct
	 * @param \Runalyze\Model\Swimdata\Entity $object
	 */
	public function __construct(Entity $object) {
		parent::__construct($object);
	}

	/**
	 * Current stroke
	 * @return int
	 */
	public function stroke() {
		return $this->current(Entity::STROKE);
	}
        
	/**
	 * Current stroke
	 * @return int
	 */
	public function stroketype() {
		return $this->current(Entity::STROKETYPE);
	}

	/**
	 * Current swolf
	 * @return int
	 */
	public function swolf() {
		return $this->current(Entity::SWOLF);
	}

	/**
	 * @param array $data
	 * @return \Runalyze\Model\Swimdata\Entity
	 */
	protected function createNewObject(array $data) {
		return new Entity($data);
	}
}