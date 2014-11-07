<?php
/**
 * This file contains class::ObjectWithID
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Abstract object with ID
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class ObjectWithID extends Object {
	/**
	 * ID
	 * @var int
	 */
	protected $ID = null;

	/**
	 * Construct
	 * @param array $data
	 */
	public function __construct(array $data = array()) {
		parent::__construct($data);

		if (isset($data['id'])) {
			$this->ID = $data['id'];
		}
	}

	/**
	 * Has ID?
	 * @return bool
	 */
	public function hasID() {
		return !is_null($this->ID);
	}

	/**
	 * ID
	 * @return int
	 */
	public function id() {
		return $this->ID;
	}
}