<?php
/**
 * This file contains class::StringObject
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Abstract string object
 * 
 * A string object represents a complete object that can be created by strings
 * and be saved as string.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class StringObject {
	/**
	 * Construct
	 * @param string $data string
	 */
	public function __construct($data = '') {
		$this->fromString($data);
	}

	/**
	 * From string
	 * @param string $string
	 */
	abstract public function fromString($string);

	/**
	 * As string
	 * @return string
	 */
	abstract public function asString();

	/**
	 * Is empty
	 * @return boolean
	 */
	public function isEmpty() {
		return ($this->asString() == '');
	}
}
