<?php
/**
 * This file contains class::Calculator
 * @package Runalyze\Calculation\Power
 */

namespace Runalyze\Calculation\Power;

use Runalyze\Model\Trackdata;
use Runalyze\Model\Route;
use Runalyze\Calculation\Distribution\TimeSeries;

/**
 * Calculate virtual power
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Power
 */
class Calculator {
	/**
	 * Acceleration due to gravity
	 * @var float [m/s^2]
	 */
	const GRAVITY = 9.8;

	/**
	 * Coefficient of rolling resistance
	 * Typically 0.004 but can be as high as 0.008 for bad asphalt or as low as 0.001 for a wooden track
	 * @var float
	 */
	const CRR = 0.004;

	/**
	 * Drag coefficient
	 * @var float
	 */
	const CW = 0.5;

	/**
	 * Effective frontal area of the rider and bicycle
	 * @var float [m^2]
	 */
	const AREA = 0.5;

	/**
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Trackdata;

	/**
	 * @var \Runalyze\Model\Route\Entity
	 */
	protected $Route;

	/**
	 * @var int
	 */
	protected $Size;

	/**
	 * @var array
	 */
	protected $Power = array();

	/**
	 * Calculator for activity properties
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param \Runalyze\Model\Route\Entity $route
	 */
	public function __construct(
		Trackdata\Entity $trackdata,
		Route\Entity $route = null
	) {
		$this->Trackdata = $trackdata;
		$this->Route = $route;

		$this->Size = $trackdata->num();
	}

	/**
	 * @return boolean
	 */
	protected function knowsRoute() {
		return (null !== $this->Route);
	}

	/**
	 * Air density
	 * Depends on temperature and  barometric pressure.
	 * Some typical values are sea level: 1.226, 1500m: 1.056 and 3000m: 0.905
	 * @return float [kg/m]
	 */
	protected function rho() {
		return 1.226;
	}

	/**
	 * Calculate power array
	 * 
	 * A constant factor of 1.5 was used in previous versions - and I don't know why.
	 * Without this factor results equal standard tools.
	 * 
	 * @author Nils Frohberg
	 * @author Hannes Christiansen
	 * @see http://www.blog.ultracycle.net/2010/05/cycling-power-calculations
	 * @param int $weight [kg] Weight of rider and bike
	 * @param float $powerFactor constant factor
	 * @return array
	 */
	public function calculate($weight = 75, $powerFactor = 1.0) {
		if (!$this->Trackdata->has(Trackdata\Entity::TIME) || !$this->Trackdata->has(Trackdata\Entity::DISTANCE)) {
			return [];
		}

		$everyNthPoint  = ceil($this->Size/1000);
		$n              = $everyNthPoint;
		$grade          = 0;
		$calcGrade      = $this->knowsRoute() && $this->Route->hasElevations();

		$ArrayTime = $this->Trackdata->time();
		$ArrayDist = $this->Trackdata->distance();
		$ArrayElev = $this->knowsRoute() ? $this->Route->elevations() : array();

		$Frl  = $weight * self::GRAVITY * self::CRR;
		$Fwpr = 0.5 * self::AREA * self::CW * $this->rho();
		$Fslp = $weight * self::GRAVITY;

		for ($i = 0; $i < $this->Size - 1; $i++) {
			if ($i%$everyNthPoint == 0) {
				if ($i + $n > $this->Size - 1) {
					$n = $this->Size - $i - 1;
				}	

				$distance = ($ArrayDist[$i+$n] - $ArrayDist[$i]) * 1000;
				$grade = ($distance == 0 || !$calcGrade) ? 0 : ($ArrayElev[$i+$n] - $ArrayElev[$i]) / $distance;
			}

			$distance = $ArrayDist[$i+1] - $ArrayDist[$i];
			$time = $ArrayTime[$i+1] - $ArrayTime[$i];

			if ($time > 0) {
				$Vmps = $distance * 1000 / $time;
				$Fw   = $Fwpr * $Vmps * $Vmps;
				$Fsl  = $Fslp * $grade;
				$this->Power[] = round(max($powerFactor * ($Frl + $Fw + $Fsl) * $Vmps, 0));
			} else {
				$this->Power[] = 0;
			}
		}

		$this->Power[] = $this->Power[$this->Size-2]; /* XXX */

		return $this->Power;
	}

	/**
	 * @return array
	 */
	public function powerData() {
		return $this->Power;
	}

	/**
	 * Calculate average power
	 * @return int [W]
	 */
	public function average() {
		if (empty($this->Power)) {
			return 0;
		}

		$Series = new TimeSeries($this->Power, $this->Trackdata->time());
		$Series->calculateStatistic();

		return round($Series->mean());
	}
}