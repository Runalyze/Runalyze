<?php
/**
 * This file contains class::NoSmoothing
 * @package Runalyze\Data\Elevation\Calculation
 */

namespace Runalyze\Data\Elevation\Calculation;

/**
 * Smoothing strategy: no smoothing
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Elevation\Calculation
 */
class NoSmoothing extends Strategy {
	/**
	 * Smooth data
	 */
	public function runSmoothing() {
		$this->SmoothedData = $this->ElevationData;
		$this->SmoothingIndices = array_keys($this->SmoothedData);
	}
}