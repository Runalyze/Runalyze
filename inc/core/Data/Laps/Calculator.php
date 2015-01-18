<?php
/**
 * This file contains class::Calculator
 * @package Runalyze\Data\Laps
 */

namespace Runalyze\Data\Laps;

use Runalyze\Model\Trackdata;
use Runalyze\Model\Route;
use Runalyze\Data\Elevation;

/**
 * Calculate laps from trackdata/route
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Laps
 */
class Calculator {
	/**
	 * @var \Runalyze\Data\Laps\Laps
	 */
	protected $Laps;

	/**
	 * @var array
	 */
	protected $Distances = array();

	/**
	 * @var \Runalyze\Model\Trackdata\Loop
	 */
	protected $TrackdataLoop = null;

	/**
	 * @var \Runalyze\Model\Route\Loop
	 */
	protected $RouteLoop = null;

	/**
	 * @param \Runalyze\Data\Laps\Laps $object
	 */
	public function __construct(Laps $object) {
		$this->Laps = $object;
	}

	/**
	 * @param array $lapDistances
	 */
	public function setDistances(array $lapDistances) {
		if (!$this->isConsecutive($lapDistances)) {
			$this->makeDistancesConsecutive($lapDistances);
		}

		$this->Distances = $lapDistances;
	}

	/**
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 * @param \Runalyze\Model\Route\Object $route
	 */
	public function calculateFrom(Trackdata\Object $trackdata, Route\Object $route = null) {
		$this->TrackdataLoop = new Trackdata\Loop($trackdata);
		$this->RouteLoop = !is_null($route) ? new Route\Loop($route) : null;

		foreach ($this->Distances as $kilometer) {
			$this->move($kilometer);
			$this->readLap();
		}
	
		if (!$this->TrackdataLoop->isAtEnd()) {
			$this->finish();
		}
	}

	/**
	 * Read lap
	 */
	protected function readLap() {
		$Lap = new Lap(
			$this->TrackdataLoop->difference(Trackdata\Object::TIME),
			$this->TrackdataLoop->difference(Trackdata\Object::DISTANCE)
		);

		$Lap->setTrackDuration($this->TrackdataLoop->time());
		$Lap->setTrackDistance($this->TrackdataLoop->distance());
		$Lap->setHR($this->TrackdataLoop->average(Trackdata\Object::HEARTRATE), $this->TrackdataLoop->max(Trackdata\Object::HEARTRATE));
		$this->addElevationFor($Lap);

		$this->Laps->add($Lap);
	}

	/**
	 * @param \Runalyze\Data\Laps\Lap $Lap
	 */
	protected function addElevationFor(Lap $Lap) {
		if ($this->RouteLoop == null) {
			return;
		}

		$Calculator = new Elevation\Calculation\Calculator($this->RouteLoop->sliceElevation());
		$Calculator->calculate();

		$Lap->setElevation($Calculator->elevationUp(), $Calculator->elevationDown());
	}

	/**
	 * @param float $kilometer
	 */
	protected function move($kilometer) {
		$this->TrackdataLoop->moveToDistance($kilometer);

		if (!is_null($this->RouteLoop)) {
			$this->RouteLoop->goToIndex($this->TrackdataLoop->index());
		}
	}

	/**
	 * Go to end and read last lap
	 */
	protected function finish() {
		$this->TrackdataLoop->goToEnd();

		if (!is_null($this->RouteLoop)) {
			$this->RouteLoop->goToEnd();
		}

		$this->readLap();
	}

	/**
	 * @param array $distances
	 */
	protected function makeDistancesConsecutive(array &$distances) {
		$sum = 0;
		$num = count($distances);

		for ($i = 0; $i < $num; ++$i) {
			$sum += $distances[$i];
			$distances[$i] = $sum;
		}
	}

	/**
	 * Is the given array consecutive?
	 * @param array $data
	 * @return boolean true for e.g. [1, 2, 3, 4, 5], false for e.g. [1, 2, 3, 2, 1]
	 */
	protected function isConsecutive(array $data) {
		$num = count($data);

		for ($i = 1; $i < $num; ++$i) {
			if ($data[$i] <= $data[$i-1]) {
				return false;
			}
		}

		return true;
	}
}