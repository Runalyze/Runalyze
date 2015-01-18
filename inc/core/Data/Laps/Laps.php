<?php
/**
 * This file contains class::Laps
 * @package Runalyze\Data\Laps
 */

namespace Runalyze\Data\Laps;

use Runalyze\Model\Trackdata;
use Runalyze\Model\Route;
use Runalyze\Model\Activity\Splits;

/**
 * Object of laps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Laps
 */
class Laps {
	/**
	 * @var \Runalyze\Data\Laps\Lap[]
	 */
	protected $Objects;

	/**
	 * @param array $distances
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 * @param \Runalyze\Model\Route\Object $route
	 */
	public function calculateFrom(array $distances, Trackdata\Object $trackdata, Route\Object $route = null) {
		$Calculator = new Calculator($this);
		$Calculator->setDistances($distances);
		$Calculator->calculateFrom($trackdata, $route);
	}

	/**
	 * @param \Runalyze\Model\Activity\Splits\Object $splits
	 */
	public function readFrom(Splits\Object $splits) {
		$SplitsReader = new SplitsReader($this);
		$SplitsReader->readFrom($splits);
	}

	/**
	 * @param \Runalyze\Data\Laps\Lap $object
	 */
	public function add(Lap $object) {
		$this->Objects[] = $object;
	}

	/**
	 * @param int $index
	 * @return \Runalyze\Data\Laps\Lap
	 * @throws \InvalidArgumentException
	 */
	public function at($index) {
		if (!is_numeric($index) || $index < 0 || $index > $this->num()) {
			throw new \InvalidArgumentException('Index out of bounds.');
		}

		return $this->Objects[$index];
	}

	/**
	 * @return int
	 */
	public function num() {
		return count($this->Objects);
	}
}