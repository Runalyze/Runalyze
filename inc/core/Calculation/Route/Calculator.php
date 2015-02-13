<?php
/**
 * This file contains class::Calculator
 * @package Runalyze\Calculation\Route
 */

namespace Runalyze\Calculation\Route;

use Runalyze\Model\Route;
use Runalyze\Data\Elevation\Correction\Corrector;
use Runalyze\Data\Elevation\Calculation;

/**
 * Calculate properties of route object
 * 
 * This calculator and correct and compute elevation
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Route
 */
class Calculator {
	/**
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $Route;

	/**
	 * Calculator for route properties
	 * @param \Runalyze\Model\Route\Object $route
	 */
	public function __construct(Route\Object $route) {
		$this->Route = $route;

		// TODO:
		// - check position data (remove if all points are at 0.0/0.0, ... ?)
	}

	/**
	 * Calculate elevation value
	 * 
	 * This method does directly update the route object.
	 */
	public function calculateElevation() {
		$Calculator = new Calculation\Calculator($this->Route->elevations());
		$Calculator->calculate();

		$this->Route->set(Route\Object::ELEVATION, $Calculator->totalElevation());
		$this->Route->set(Route\Object::ELEVATION_UP, $Calculator->elevationUp());
		$this->Route->set(Route\Object::ELEVATION_DOWN, $Calculator->elevationDown());
	}

	/**
	 * Correct elevation data
	 * 
	 * This method does directly update the route object.
	 * 
	 * @return boolean false if correction did not work
	 */
	public function tryToCorrectElevation() {
		if (!$this->Route->hasPositionData()) {
			return false;
		}

		$Corrector = new Corrector();
		$Corrector->correctElevation($this->Route->latitudes(), $this->Route->longitudes());
		$result = $Corrector->getCorrectedElevation();

		if (!empty($result)) {
			$this->Route->set(Route\Object::ELEVATIONS_CORRECTED, $result);
			$this->Route->set(Route\Object::ELEVATIONS_SOURCE, $Corrector->getNameOfUsedStrategy());

			return true;
		}

		return false;
	}
}