<?php
/**
 * This file contains class::Threshold
 * @package Runalyze\Data\Elevation\Calculation
 */

namespace Runalyze\Data\Elevation\Calculation;

/**
 * Smoothing strategy: Threshold
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Elevation\Calculation
 */
class Threshold extends Strategy {
	/**
	 * Epsilon
	 * @var float
	 */
	protected $Epsilon = 0;

	/**
	 * Construct
	 * @param array $elevation
	 * @param float $epsilon [optional]
	 */
	public function __construct(array $elevation, $epsilon = 0) {
		parent::__construct($elevation);

		$this->setEpsilon($epsilon);
	}

	/**
	 * Set epsilon
	 * @param float $epsilon
	 */
	public function setEpsilon($epsilon) {
		$this->Epsilon = $epsilon;
	}

	/**
	 * Smooth data
	 */
	public function runSmoothing() {
		$i = 0;
		$this->SmoothedData = array($this->ElevationData[0]);
		$this->SmoothingIndices = array(0);

		while (isset($this->ElevationData[$i+1])) {
			$isLastStepUp    = $this->ElevationData[$i] > end($this->SmoothedData) && $this->ElevationData[$i+1] <= $this->ElevationData[$i];
			$isLastStepDown  = $this->ElevationData[$i] < end($this->SmoothedData) && $this->ElevationData[$i+1] >= $this->ElevationData[$i];
			$isAboveTreshold = abs(end($this->SmoothedData) - $this->ElevationData[$i]) > $this->Epsilon;

			if (($isLastStepUp || $isLastStepDown) && $isAboveTreshold) {
				$this->SmoothingIndices[] = $i;
				$this->SmoothedData[] = $this->ElevationData[$i];
			}

			$i++;
		}

		$this->SmoothingIndices[] = $i;
		$this->SmoothedData[] = $this->ElevationData[$i];
	}
}