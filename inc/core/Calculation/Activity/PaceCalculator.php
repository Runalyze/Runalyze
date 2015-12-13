<?php
/**
 * This file contains class::PaceCalculator
 * @package Runalyze\Calculation\Activity
 */

namespace Runalyze\Calculation\Activity;

use Runalyze\Model\Trackdata;

/**
 * Calculate pace array
 * 
 * This pace calculator is a simple application of the PaceSmoother class.
 * To ensure a smooth pace for bad gps data, provided for example by Runtastic,
 * the smoother does move at least 1m at each step.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Activity
 */
class PaceCalculator {
	/**
	 * @var \Runalyze\Calculation\Activity\PaceSmoother
	 */
	protected $Smoother = null;

	/**
	 * @var array
	 */
	protected $Pace = array();

	/**
	 * Calculate pace array
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 */
	public function __construct(Trackdata\Entity $trackdata) {
		if ($trackdata->has(Trackdata\Entity::TIME) && $trackdata->has(Trackdata\Entity::DISTANCE)) {
			$this->Smoother = new PaceSmoother($trackdata, true);
		}
	}

	/**
	 * Calculate pace array
	 * @return array
	 */
	public function calculate() {
		if (null !== $this->Smoother) {
			$this->Pace = $this->Smoother->smooth(0.001, PaceSmoother::MODE_DISTANCE);
		}

		return $this->Pace;
	}

	/**
	 * @return array
	 */
	public function result() {
		return $this->Pace;
	}
}