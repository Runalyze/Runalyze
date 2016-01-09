<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Abstract entity
 * 
 * An object represents a set of properties, e.g. a row from database.
 * The internal data array contains all raw values, only arrays are automatically transformed.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class Entity {
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
	 * Flag: ensure arrays to be equally sized
	 * @var bool
	 */
	protected $checkArraySizes = false;

	/**
	 * Construct
	 * @param array $data
	 */
	public function __construct(array $data = array()) {
		$this->readData($data);

		if ($this->checkArraySizes) {
			$this->checkArraySizes();
		} else {
			$this->count();
		}
	}

	/**
	 * Create deep copies of internal objects
	 */
	protected function cloneInternalObjects() {
		foreach ($this as $property => $value) {
			if (is_object($value)) {
				$this->{$property} = clone $value;
			}
		}
	}

	/**
	 * Read data
	 * @param array $data
	 */
	final protected function readData(array $data) {
		foreach ($this->properties() as $key) {
			if (isset($data[$key]) && $data[$key] !== '') {
				$this->Data[$key] = $this->isArray($key) && !is_array($data[$key]) ? self::explode($data[$key]) : $data[$key];
			} else {
				$this->Data[$key] = $this->isArray($key) ? array() : ($this->canBeNull($key) ? null : '');
			}
		}
	}

	/**
	 * Count number of points
	 */
	protected function count() {
		$this->numberOfPoints = 0;

		foreach ($this->properties() as $key) {
			if ($this->isArray($key)) {
				$num = count($this->Data[$key]);

				if ($num > $this->numberOfPoints) {
					$this->numberOfPoints = $num;
				}
			}
		}
	}

	/**
	 * Number of points
	 * @return int
	 */
	public function num() {
		return $this->numberOfPoints;
	}

	/**
	 * Check array sizes
	 * @throws \RuntimeException
	 */
	protected function checkArraySizes() {
		$this->numberOfPoints = 0;

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
	 * Ensure numeric values
	 * @param array|string $keyOrKeys key or array of keys to be checked
	 * @param int|float $defaultValue optional, default: 0
	 */
	protected function ensureNumericValue($keyOrKeys, $defaultValue = 0) {
		if (!is_array($keyOrKeys)) {
			$keyOrKeys = array($keyOrKeys);
		}

		foreach ($keyOrKeys as $key) {
			if (array_key_exists($key, $this->Data)) {
				if (!is_numeric($this->Data[$key])) {
					if (is_bool($this->Data[$key])) {
						$this->Data[$key] = (int)$this->Data[$key];
					} else {
						$this->Data[$key] = $defaultValue;
					}
				} else {
					$this->Data[$key] = (float)$this->Data[$key];
				}
			}
		}
	}

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
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
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
		if (!array_key_exists($key, $this->Data)) {
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
			$this->handleNewArraySize(count($this->Data[$key]));
		}
	}

	/**
	 * Handle a new array size
	 * @param int $num
	 */
	protected function handleNewArraySize($num) {
		if ($num != $this->numberOfPoints) {
			if ($this->checkArraySizes) {
				$this->checkArraySizes();
			} else {
				$this->count();
			}
		}
	}

	/**
	 * Get value for this key
	 * @param string $key
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function get($key) {
		if (!array_key_exists($key, $this->Data)) {
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
		if (!array_key_exists($key, $this->Data)) {
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
	 * Is this object empty?
	 * @return boolean
	 */
	public function isEmpty() {
		foreach ($this->properties() as $key) {
			if (!empty($this->Data[$key]) && !$this->ignoreNonEmptyValue($key)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Ignore a key while checking for emptiness
	 * @param string $key
	 * @return boolean
	 */
	protected function ignoreNonEmptyValue($key) {
		return false;
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
	public static function explode($string) {
		return explode(self::ARRAY_SEPARATOR, $string);
	}

	/**
	 * Implode array to string
	 * @param array $data
	 * @return string
	 */
	public static function implode(array $data) {
		return implode(self::ARRAY_SEPARATOR, $data);
	}
}