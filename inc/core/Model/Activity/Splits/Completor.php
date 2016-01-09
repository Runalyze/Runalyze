<?php
/**
 * This file contains class::Completor
 * @package Runalyze\Model\Activity\Splits
 */

namespace Runalyze\Model\Activity\Splits;

/**
 * Completor for splits: fill missing values from trackdata array
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity\Splits
 */
abstract class Completor {
	/**
	 * Mode: time
	 * @var string
	 */
	const MODE_TIME = 'time';

	/**
	 * Mode: distance
	 * @var string
	 */
	const MODE_DISTANCE = 'distance';

	/**
	 * Time
	 * @var array
	 */
	protected $Time;

	/**
	 * Distance
	 * @var array
	 */
	protected $Distance;

	/**
	 * Splits
	 * @var \Runalyze\Model\Activity\Splits\Entity
	 */
	protected $Splits;

	/**
	 * Construct
	 * @param \Runalyze\Model\Activity\Splits\Entity
	 * @param array $time
	 * @param array $distance
	 */
	public function __construct(Entity $splits, array $time, array $distance) {
		$this->Splits = $splits;
		$this->Time = $time;
		$this->Distance = $distance;
	}

	/**
	 * Mode
	 * @return string
	 */
	abstract public function mode();

	/**
	 * Complete splits
	 */
	public function completeSplits() {
		$totalDistance = 0;
		$totalTime = 0;
		$size = min(count($this->Time), count($this->Distance));
		$i = 0;

		foreach ($this->Splits->asArray() as $split) {
			if ($this->mode() == self::MODE_DISTANCE) {
				$s = $split->time();

				while ($i < $size-1 && $s > $this->Time[$i] - $totalTime) {
					$i++;
				}

				$split->setDistance($this->Distance[$i] - $totalDistance);
			} else {
				$dist = $split->distance();

				while ($i < $size-1 && $dist > $this->Distance[$i] - $totalDistance) {
					$i++;
				}

				$split->setTime($this->Time[$i] - $totalTime);
			}

			$totalTime     = $this->Time[$i];
			$totalDistance = $this->Distance[$i];
		}
	}
}