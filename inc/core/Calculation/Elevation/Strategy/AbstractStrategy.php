<?php
/**
 * This file contains class::Strategy
 * @package Runalyze\Calculation\Elevation\Strategy
 */

namespace Runalyze\Calculation\Elevation\Strategy;

/**
 * Abstract strategy to smooth elevation data for calculation
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Elevation\Strategy
 */
abstract class AbstractStrategy
{
	/**
	 * Original elevation data
	 * @var array
	 */
	protected $ElevationData = array();

	/**
	 * Smoothed data
	 * @var array
	 */
	protected $SmoothedData = array();

	/**
	 * Indices for smoothing
	 * @var array
	 */
	protected $SmoothingIndices = array();

	/**
	 * Construct
	 * @param array $elevation
	 */
	public function __construct(array $elevation)
	{
		$this->ElevationData = $elevation;
	}

	/**
	 * Smooth data
	 */
	abstract public function runSmoothing();

	/**
	 * Get smoothed data
	 * @return array
	 */
	final public function smoothedData()
	{
		return $this->SmoothedData;
	}

	/**
	 * Get used indices
	 * @return array
	 */
	final public function smoothingIndices()
	{
		return $this->SmoothingIndices;
	}

	/**
	 * Perpendicular distance from point to line
	 * @param float $pointX
	 * @param float $pointY
	 * @param float $line1x
	 * @param float $line1y
	 * @param float $line2x
	 * @param float $line2y
	 * @return float
	 */
	final protected function perpendicularDistance($pointX, $pointY, $line1x, $line1y, $line2x, $line2y)
	{
		if ($line2x == $line1x) {
			return abs($pointX - $line2x);
		}

		$slope = ($line2y - $line1y) / ($line2x - $line1x);
        $passThroughY = -$line1x * $slope + $line1y;

		return (abs(($slope * $pointX) - $pointY + $passThroughY)) / (sqrt($slope*$slope + 1));
	}
}
