<?php
/**
 * This file contains class::Object
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Abstract object
 * 
 * An object represents a set of properties, e.g. a row from database.
 * The internal data array contains all raw values, only arrays are automatically transformed.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class Object {
	/**
	 * Array separator
	 * @var string
	 */
	const ARRAY_SEPARATOR = '|';

	/**
	 * Raw data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Number of data points
	 * @var int
	 */
	protected $numberOfPoints = 0;

	/**
	 * Construct
	 * @param array $data
	 */
	public function __construct(array $data = array()) {
		$this->readData($data);
	}

	/**
	 * Read data
	 * @param array $data
	 */
	final protected function readData(array $data) {
		foreach ($this->properties() as $key) {
			if (isset($data[$key]) && $data[$key] != '') {
				$this->Data[$key] = $this->isArray($key) && !is_array($data[$key]) ? self::explode($data[$key]) : $data[$key];
			} else {
				$this->Data[$key] = $this->isArray($key) ? array() : '';
			}
		}
	}

	/**
	 * Check array sizes
	 * @throws \RuntimeException
	 */
	protected function checkArraySizes() {
		foreach ($this->properties() as $key) {
			if ($this->isArray($key)) {
				try {
					$this->checkArraySize( count($this->Data[$key]) );
				} catch(\RuntimeException $E) {
					throw new \RuntimeException($E->getMessage().' (for '.$key.')');
				}
			}
		}
	}

	/**
	 * Check array size
	 * @param int $num
	 * @throws \RuntimeException
	 */
	protected function checkArraySize($num) {
		if ($num == 0) {
			return;
		}

		if ($this->numberOfPoints > 0 && $num != $this->numberOfPoints) {
			throw new \RuntimeException('Data arrays must be of the same size. ('.$num.' != '.$this->numberOfPoints.')');
		} else {
			$this->numberOfPoints = $num;
		}
	}

	/**
	 * Properties
	 * @return array
	 */
	abstract public function properties();

	/**
	 * Synchronize internal models
	 */
	public function synchronize() {}

	/**
	 * Can set key?
	 * @param string $key
	 * @return boolean
	 */
	protected function canSet($key) {
		return true;
	}

	/**
	 * Is the property an array?
	 * @param string $key
	 * @return boolean
	 */
	public function isArray($key) {
		return false;
	}

	/**
	 * Set array
	 * @param string $key
	 * @param mixed $value
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public function set($key, $value) {
		if (!isset($this->Data[$key])) {
			throw new \InvalidArgumentException('Unkown data index "'.$key.'".');
		}

		if (!$this->canSet($key)) {
			throw new \InvalidArgumentException('"'.$key.'" can not be set.');
		}

		if ($this->isArray($key) && !is_array($value)) {
			throw new \RuntimeException('Value "'.$key.'" must be provided as array.');
		}

		$this->Data[$key] = $value;

		if ($this->isArray($key)) {
			$this->checkArraySize(count($this->Data[$key]));
		}
	}

	/**
	 * Get value for this key
	 * @param string $key
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function get($key) {
		if (!isset($this->Data[$key])) {
			throw new \InvalidArgumentException('Unknown data index "'.$key.'".');
		}

		return $this->Data[$key];
	}

	/**
	 * Has array for this key
	 * @param string $key
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function has($key) {
		if (!isset($this->Data[$key])) {
			throw new \InvalidArgumentException('Unknown data index "'.$key.'".');
		}

		return !empty($this->Data[$key]);
	}

	/**
	 * Clear
	 */
	public function clear() {
		foreach ($this->properties() as $key) {
			if ($this->isArray($key)) {
				$this->Data[$key] = array();
			} else {
				$this->Data[$key] = '';
			}
		}

		$this->numberOfPoints = 0;
	}

	/**
	 * Get complete data
	 * @return array
	 */
	public function completeData() {
		return $this->Data;
	}

	/**
	 * Explode string to array
	 * @param string $string
	 * @return array
	 */
	static public function explode($string) {
		return explode(self::ARRAY_SEPARATOR, $string);
	}

	/**
	 * Implode array to string
	 * @param array $data
	 * @return string
	 */
	static public function implode(array $data) {
		return implode(self::ARRAY_SEPARATOR, $data);
	}
}