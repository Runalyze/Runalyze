<?php
/**
 * This file contains class::StringObject
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Abstract string array object
 * 
 * A string object represents a complete object consisting mainly of an array
 * that can be created by strings and be saved as string.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class StringArrayObject extends StringObject {
	/**
	 * Single elements
	 * @var mixed
	 */
	protected $Elements = array();

	/**
	 * Construct
	 * @param mixed $data string or array
	 */
	public function __construct($data = '') {
		if (is_array($data)) {
			$this->fromArray($data);
		} elseif (!empty($data)) {
			parent::__construct($data);
		}
	}

	/**
	 * From array
	 * @param array[] $data array of elements
	 */
	public function fromArray(array $data) {
		$this->Elements = $data;
	}

	/**
	 * As array
	 * @return array
	 */
	public function asArray() {
		return $this->Elements;
	}

	/**
	 * From string
	 * @param string $string
	 */
	public function fromString($string) {
		$json = json_decode($string, true);

		if (is_array($json)) {
			$this->fromArray($json);
		} else {
			$this->clear();
		}
	}

	/**
	 * As string
	 * @return string
	 */
	public function asString() {
		if ($this->isEmpty()) {
			return '';
		}

		return json_encode($this->asArray(), JSON_HEX_QUOT);
	}

	/**
	 * Clear
	 */
	public function clear() {
		$this->Elements = array();
	}

	/**
	 * Number of pauses
	 * @return int
	 */
	public function num() {
		return count($this->Elements);
	}

	/**
	 * Is empty
	 * @return boolean
	 */
	public function isEmpty() {
		return ($this->num() == 0);
	}

	/**
	 * Add
	 * @param mixed $element
	 */
	public function add($element) {
		$this->Elements[] = $element;
	}

	/**
	 * Get pause
	 * @param int $index
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function at($index) {
		if (!isset($this->Elements[$index])) {
			throw new \InvalidArgumentException('Unknown object index.');
		}

		return $this->Elements[$index];
	}
}