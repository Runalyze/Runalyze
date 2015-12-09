<?php
/**
 * This file contains class::SplitsReader
 * @package Runalyze\Data\Laps
 */

namespace Runalyze\Data\Laps;

use Runalyze\Model\Activity\Splits;

/**
 * Read laps from splits object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Laps
 */
class SplitsReader {
	/**
	 * @var \Runalyze\Data\Laps\Laps
	 */
	protected $Laps;

	/**
	 * @param \Runalyze\Data\Laps\Laps $object
	 */
	public function __construct(Laps $object) {
		$this->Laps = $object;
	}

	/**
	 * @param \Runalyze\Model\Activity\Splits\Entity $Splits
	 */
	public function readFrom(Splits\Entity $Splits) {
		foreach ($Splits->asArray() as $Split) {
			$this->Laps->add(new Lap(
				$Split->time(),
				$Split->distance(),
				$Split->isActive() ? Lap::MODE_ACTIVE : Lap::MODE_RESTING
			));
		}
	}
}