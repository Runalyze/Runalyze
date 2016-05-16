<?php
/**
 * This file contains class::AbstractStrategy
 * @package Runalyze\Service\ElevationCorrection\Strategy
 */

namespace Runalyze\Service\ElevationCorrection\Strategy;

/**
 * Abstract strategy to correct elevation data
 *
 * @author Hannes Christiansen
 * @package Runalyze\Service\ElevationCorrection\Strategy
 */
abstract class AbstractStrategy
{
	/** @var array */
	protected $LatitudePoints = array();

	/** @var array */
	protected $LongitudePoints = array();

	/** @var array */
	protected $ElevationPoints = array();

	/**
	 * Points to group together
	 *
	 * This is only a bad guess.
	 * It would be better to decide this by distance between the points.
	 * @var int
	 */
	protected $POINTS_TO_GROUP = 5;

	/**
	 * Construct
	 * @param array $LatitudePoints
	 * @param array $LongitudePoints
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $LatitudePoints, array $LongitudePoints)
	{
		if (empty($LatitudePoints) || empty($LongitudePoints)) {
			throw new \InvalidArgumentException('Latitudes/longitudes must not be empty.');
		}

		if (count($LatitudePoints) != count($LongitudePoints)) {
			throw new \InvalidArgumentException('Latitudes and longitudes must be of same size.');
		}

		$this->LatitudePoints = $LatitudePoints;
		$this->LongitudePoints = $LongitudePoints;
	}

	/**
	 * Can the strategy handle the data?
	 * @return boolean
	 */
	abstract public function canHandleData();

	/**
	 * Correct elevation
	 */
	abstract public function correctElevation();

	/**
	 * Guess unknown elevations
	 * @param int $unknownValue
	 */
	public function guessUnknown($unknownValue = -32768)
	{
		$numberOfPoints = count($this->ElevationPoints);
		$i = 0;

		while ($i < $numberOfPoints && $this->ElevationPoints[$i] == $unknownValue) { // unknown from the start
			$i++;
		};

		if ($i == $numberOfPoints) { // in case nothing is known assume elevation of 0
			$lastKnown = 0;
		} else {
			$lastKnown = $this->ElevationPoints[$i];  // first good one will be used for beginning
		}

		for ($i = 0; $i < $numberOfPoints; $i++) {	//substitute each unknown with last known
			if ($this->ElevationPoints[$i] == $unknownValue) {
				$this->ElevationPoints[$i] = $lastKnown;
			} else {
				$lastKnown = $this->ElevationPoints[$i];
			}
		}
	}

	/**
	 * Get corrected elevation
	 * @return array
	 */
	final public function getCorrectedElevation()
	{
		return $this->ElevationPoints;
	}
}
