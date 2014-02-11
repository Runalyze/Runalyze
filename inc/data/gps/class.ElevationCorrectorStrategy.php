<?php
/**
 * This file contains class::ElevationCorrectorStrategy
 * @package Runalyze\Data\GPS\Elevation
 */
/**
 * Elevation corrector strategy
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS\Elevation
 */
abstract class ElevationCorrectorStrategy {
	/**
	 * Latitude points
	 * @var array
	 */
	protected $LatitudePoints = array();

	/**
	 * Longitude points
	 * @var array
	 */
	protected $LongitudePoints = array();

	/**
	 * Elevation points
	 * @var array
	 */
	protected $ElevationPoints = array();

	/**
	 * Points to group together
	 * 
	 * This is only a bad guess. It would be better to decide this by distance between the points.
	 * @var int
	 */
	protected $POINTS_TO_GROUP = 5;

	/**
	 * Construct
	 * @param array $LatitudePoints
	 * @param array $LongitudePoints
	 */
	public function __construct(array $LatitudePoints, array $LongitudePoints) {
		if (empty($LatitudePoints) || empty($LongitudePoints))
			throw new InvalidArgumentException('Latitudes/longitudes must not be empty.');

		if (count($LatitudePoints) != count($LongitudePoints))
			throw new InvalidArgumentException('Latitudes and longitudes must be of same size.');

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
	 * Get corrected elevation
	 * @return array
	 */
	final public function getCorrectedElevation() {
		return $this->ElevationPoints;
	}
}