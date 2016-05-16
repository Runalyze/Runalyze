<?php
/**
 * This file contains class::DouglasPeucker
 * @package Runalyze\Calculation\Elevation\Strategy
 */

namespace Runalyze\Calculation\Elevation\Strategy;

/**
 * Smoothing strategy: Douglas-Peucker
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Elevation\Strategy
 * @see http://en.wikipedia.org/wiki/Ramer%E2%80%93Douglas%E2%80%93Peucker_algorithm
 */
class DouglasPeucker extends AbstractStrategy
{
	/**
	 * Distance (in x direction) between two points
	 * @var int
	 */
	const DISTANCE_BETWEEN_POINTS = 50;

	/**
	 * Epsilon
	 * @var float|int
	 */
	protected $Epsilon = 0;

	/**
	 * Construct
	 * @param array $elevation
	 * @param float|int $epsilon [optional]
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
		$this->SmoothingIndices = array(0, count($this->ElevationData)-1);
		$this->SmoothedData = $this->smoothPart($this->ElevationData);

		sort($this->SmoothingIndices);
	}

	/**
	 * Smooth part of line
	 * @param array $data
	 * @param int $offset
	 * @return array
	 */
	protected function smoothPart(array $data, $offset = 0)
	{
		$maxDist = 0;
		$index = 0;
		$num = count($data);

		for ($i = 1; $i < ($num - 1); $i++) {
			$dist = $this->perpendicularDistance(
				self::DISTANCE_BETWEEN_POINTS*$i, $data[$i],
				self::DISTANCE_BETWEEN_POINTS*0, $data[0],
				self::DISTANCE_BETWEEN_POINTS*($num-1), $data[$num-1]
			);

			if ($dist > $maxDist) {
				$index = $i;
				$maxDist = $dist;
			}
		}

		if ($maxDist > $this->Epsilon) {
			$this->SmoothingIndices[] = $offset + $index;

			$recResults1 = $this->smoothPart(array_slice($data, 0, $index + 1), $offset);
			$recResults2 = $this->smoothPart(array_slice($data, $index, $num - $index), $offset + $index);

			return array_merge(array_slice($recResults1, 0, count($recResults1) - 1), array_slice($recResults2, 0, count($recResults2)));
		}

		return array($data[0], $data[$num-1]);
	}
}