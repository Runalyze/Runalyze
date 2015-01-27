<?php
/**
 * This file contains class::Adjustment
 * @package Runalyze\Calculation\JD\Correction
 */

namespace Runalyze\Calculation\Elevation;

use Runalyze\Configuration;

/**
 * Modifier
 *
 * Example: elevation +100/-50m
 * Correction: positive +2, negative -1
 * Result: adds 2*100 -1*50 = 150m to the distance
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Elevation
 */
class DistanceModifier {
	/**
	 * Distance to add for 1m positive elevation
	 * @var int
	 */
	protected $PositiveCorrector = 0;

	/**
	 * Distance to add for 1m negative elevation
	 * @var int
	 */
	protected $NegativeCorrector = 0;

	/**
	 * Distance
	 * @var float
	 */
	protected $Distance;

	/**
	 * Elevation up
	 * @var int
	 */
	protected $Up;

	/**
	 * Elevation down
	 * @var int
	 */
	protected $Down;

	/**
	 * Construct
	 * @param float $distance [optional] [km]
	 * @param int $up [optional] [m]
	 * @param int $down [optional] [m]
	 * @param \Runalyze\Configuration\Category\Vdot $config [optional]
	 */
	public function __construct($distance = 0, $up = 0, $down = 0, Configuration\Category\Vdot $config = null) {
		if (!is_null($config)) {
			$this->setCorrectionValues($config->correctionForPositiveElevation(), $config->correctionForNegativeElevation());
		}

		$this->setDistance($distance);
		$this->setElevation($up, $down);
	}

	/**
	 * Distance
	 * @param float $distance [km]
	 */
	public function setDistance($distance) {
		$this->Distance = $distance;
	}

	/**
	 * Set elevation
	 * @param int $up [m]
	 * @param int $down [m]
	 */
	public function setElevation($up, $down) {
		$this->Up = $up;
		$this->Down = $down;
	}

	/**
	 * Set correction values
	 * @param int $positiveCorrection
	 * @param int $negativeCorrection [m]
	 */
	public function setCorrectionValues($positiveCorrection, $negativeCorrection) {
		$this->PositiveCorrector = $positiveCorrection;
		$this->NegativeCorrector = $negativeCorrection;
	}

	/**
	 * Additional distance
	 * @return float [km]
	 */
	public function additionalDistance() {
		return ($this->PositiveCorrector*$this->Up + $this->NegativeCorrector*$this->Down)/1000;
	}

	/**
	 * Corrected distance
	 * @return float [km]
	 */
	public function correctedDistance() {
		return $this->Distance + $this->additionalDistance();
	}
}