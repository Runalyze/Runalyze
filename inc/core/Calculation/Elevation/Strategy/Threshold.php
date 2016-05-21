<?php
/**
 * This file contains class::Threshold
 * @package Runalyze\Calculation\Elevation\Strategy
 */

namespace Runalyze\Calculation\Elevation\Strategy;

/**
 * Smoothing strategy: Threshold
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Elevation\Strategy
 */
class Threshold extends AbstractStrategy
{
	/**
	 * Epsilon
	 * @var int
	 */
	protected $Epsilon = 0;

	/**
	 * Construct
	 * @param array $elevation
	 * @param int $epsilon [optional]
	 */
	public function __construct(array $elevation, $epsilon = 0)
	{
		parent::__construct($elevation);

		$this->setEpsilon($epsilon);
	}

	/**
	 * Set epsilon
	 * @param float $epsilon
	 */
	public function setEpsilon($epsilon)
	{
		$this->Epsilon = $epsilon;
	}

	/**
	 * Smooth data
	 */
	public function runSmoothing()
	{
		$i = 0;
		$max = count($this->ElevationData);
		$this->SmoothedData = array($this->ElevationData[0]);
		$this->SmoothingIndices = array(0);

		while ($i+1 < $max) {
			$lastPoint = end($this->SmoothedData);

			// Due to performance reasons this is not clean code
			if (
				(abs($lastPoint - $this->ElevationData[$i]) > $this->Epsilon) &&
				(
					($this->ElevationData[$i] > $lastPoint && $this->ElevationData[$i+1] <= $this->ElevationData[$i]) ||
					($this->ElevationData[$i] < $lastPoint && $this->ElevationData[$i+1] >= $this->ElevationData[$i])
				)
			) {
				$this->SmoothingIndices[] = $i;
				$this->SmoothedData[] = $this->ElevationData[$i];
			}

			$i++;
		}

		$this->SmoothingIndices[] = $i;
		$this->SmoothedData[] = $this->ElevationData[$i];
	}
}