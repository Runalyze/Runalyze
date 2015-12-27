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
	 * @var \Runalyze\Model\Route\Entity
	 */
	protected $Route;

	/**
	 * Calculator for route properties
	 * @param \Runalyze\Model\Route\Entity $route
	 */
	public function __construct(Route\Entity $route) {
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

		$this->Route->set(Route\Entity::ELEVATION, $Calculator->totalElevation());
		$this->Route->set(Route\Entity::ELEVATION_UP, $Calculator->elevationUp());
		$this->Route->set(Route\Entity::ELEVATION_DOWN, $Calculator->elevationDown());
	}

	/**
	 * Correct elevation data
	 * 
	 * This method does directly update the route object.
	 * 
	 * @param string $strategyName
	 * @return boolean false if correction did not work
	 */
	public function tryToCorrectElevation($strategyName = '') {
		if (!$this->Route->hasPositionData()) {
			return false;
		}

		if ($strategyName == 'none') {
			$this->removeElevationCorrection();

			return true;
		}

		$coordinates = $this->Route->latitudesAndLongitudesFromGeohash();

		$Corrector = new Corrector();
		$Corrector->correctElevation($coordinates['lat'], $coordinates['lng'], $strategyName);
		$result = $Corrector->getCorrectedElevation();

		if (!empty($result)) {
			$this->Route->set(Route\Entity::ELEVATIONS_CORRECTED, $result);
			$this->Route->set(Route\Entity::ELEVATIONS_SOURCE, $Corrector->getNameOfUsedStrategy());

			return true;
		}

		return false;
	}

	/**
	 * Remove elevation correction
	 */
	public function removeElevationCorrection() {
		$this->Route->set(Route\Entity::ELEVATIONS_CORRECTED, array());
		$this->Route->set(Route\Entity::ELEVATIONS_SOURCE, '');
	}
}