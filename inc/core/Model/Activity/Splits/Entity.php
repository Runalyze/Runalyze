<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\Activity\Splits
 */

namespace Runalyze\Model\Activity\Splits;

use Runalyze\Model\StringArrayObject;

/**
 * Splits entity
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity\Splits
 */
class Entity extends StringArrayObject {
	/**
	 * Separator
	 * @var string
	 */
	const SEPARATOR = '-';

	/**
	 * Single objects
	 * @var \Runalyze\Model\Activity\Splits\Split[]
	 */
	protected $Elements = array();

	/**
	 * Clone object
	 */
	public function __clone() {
		foreach ($this->Elements as $i => $split) {
			$this->Elements[$i] = clone $split;
		}
	}

	/**
	 * From array
	 * @param \Runalyze\Model\Activity\Splits\Split[] $splits
	 * @throws \InvalidArgumentException
	 */
	public function fromArray(array $splits) {
		$this->clear();

		foreach ($splits as $split) {
			if (!($split instanceof Split)) {
				throw new \InvalidArgumentException('Array must consist of Split-objects only.');
			}

			if (!$split->isEmpty()) {
				$this->add($split);
			}
		}
	}

	/**
	 * As array
	 * @return \Runalyze\Model\Activity\Splits\Split[]
	 */
	public function asArray() {
		return $this->Elements;
	}

	/**
	 * From string
	 * @param string $string
	 */
	public function fromString($string) {
		$this->clear();

		$strings = explode(self::SEPARATOR, $string);
		foreach ($strings as $splitString) {
			$Split = new Split();
			$Split->fromString($splitString);

			if (!$Split->isEmpty()) {
				$this->add($Split);
			}
		}
	}

	/**
	 * As string
	 * @return string
	 */
	public function asString() {
		$array = array();

		foreach ($this->Elements as $Split) {
			$array[] = $Split->asString();
		}

		return implode(self::SEPARATOR, $array);
	}

	/**
	 * Add split
	 * @param \Runalyze\Model\Activity\Splits\Split $split
	 * @throws \InvalidArgumentException
	 */
	public function add($split) {
		if (!($split) instanceof Split) {
			throw new \InvalidArgumentException('Element to add has to be of type \'Split\'.');
		}

		parent::add($split);
	}

	/**
	 * Get split
	 * @param int $index
	 * @return \Runalyze\Model\Activity\Splits\Split
	 * @throws \InvalidArgumentException
	 */
	public function at($index) {
		return parent::at($index);
	}

	/**
	 * Get total time
	 * @return int
	 */
	public function totalTime() {
		$time = 0;

		foreach ($this->Elements as $split) {
			$time += $split->time();
		}

		return $time;
	}

	/**
	 * Get total distance
	 * @return float
	 */
	public function totalDistance() {
		$distance = 0;

		foreach ($this->Elements as $split) {
			$distance += $split->distance();
		}

		return $distance;
	}

	/**
	 * Is at least one lap active?
	 * @param int $num
	 * @return boolean
	 */
	public function hasActiveLaps($num = 1) {
		$count = 0;

		foreach ($this->Elements as $split) {
			if ($split->isActive()) {
				$count++;

				if ($count == $num) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Are there active and inactive laps?
	 * @return boolean
	 */
	public function hasActiveAndInactiveLaps() {
		$active = null;

		foreach ($this->Elements as $split) {
			if (is_null($active)) {
				$active = $split->isActive();
			} elseif ($active != $split->isActive()) {
				return true;
			}
		}

		return false;
	}
}