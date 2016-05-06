<?php
/**
 * This file contains class::FromExternalAPI
 * @package Runalyze\Service\ElevationCorrection\Strategy
 */

namespace Runalyze\Service\ElevationCorrection\Strategy;

/**
 * Abstract corrector strategy for external API
 *
 * @author Hannes Christiansen
 * @package Runalyze\Service\ElevationCorrection\Strategy
 */
abstract class AbstractStrategyFromExternalAPI extends AbstractStrategy
{
	/**
	 * Points per call
	 * @var int
	 */
	protected $POINTS_PER_CALL = 20;

	/** @var int */
	protected $UnknownValue = -32768;

	/**
	 * Correct elevation
	 *
	 * Note: canHandleData() has to be called before!
	 */
	final public function correctElevation()
	{
		$numberOfPoints = count($this->LatitudePoints);
		$pointsToGroup  = $this->POINTS_TO_GROUP * ceil($numberOfPoints/1000);
		$latitudes  = array();
		$longitudes = array();

		for ($i = 0; $i < $numberOfPoints; $i++) {
			if ($i % $pointsToGroup == 0) {
				$latitudes[]  = $this->LatitudePoints[$i];
				$longitudes[] = $this->LongitudePoints[$i];
			}

			if ( ($i+1)%($this->POINTS_PER_CALL*$pointsToGroup) == 0 || $i == $numberOfPoints - 1) {
				$result = $this->fetchElevationFor($latitudes, $longitudes);
				$points = count($result);

				for ($d = 0; $d < $points; $d++)
					for ($j = 0; $j < $pointsToGroup; $j++)
						$this->ElevationPoints[] = $result[$d];

				$latitudes = array();
				$longitudes = array();
			}
		}

		if (count($this->ElevationPoints) > $numberOfPoints)
			$this->ElevationPoints = array_slice($this->ElevationPoints, 0, $numberOfPoints);

		$this->guessUnknown($this->UnknownValue);
	}

	/**
	 * Fetch elevation
	 * @param array $latitudes
	 * @param array $longitudes
	 * @return array
	 */
	abstract protected function fetchElevationFor(array $latitudes, array $longitudes);
}
