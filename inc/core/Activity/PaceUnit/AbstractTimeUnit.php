<?php
/**
 * This file contains class::AbstractTimeUnit
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

use Runalyze\Activity\Duration;

/**
 * Abstract time pace unit
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
abstract class AbstractTimeUnit extends AbstractUnit
{
	/**
	 * Pace is displayed as time per distance
	 * @return bool
	 */
	final public function isTimeFormat()
	{
		return true;
	}

	/**
	 * Dividend to transform 's/km' to current unit
	 * 
	 * This should only be used for decimal formats.
	 * dividendForUnit() / 's/km' = [decimal pace value]
	 * 
	 * @return float
	 * @throws \RuntimeException
	 */
	public function dividendForUnit()
	{
		throw new \RuntimeException('factorForUnit() must not be used for units in decimal format');
	}

	/**
	 * Get raw value
	 * 
	 * This returns for example a float for decimal units and an integer as 's/km'
	 * for time units.
	 * 
	 * @param int $secondsPerKm
	 * @return mixed
	 */
	public function rawValue($secondsPerKm)
	{
		return $secondsPerKm * $this->factorForUnit();
	}

	/**
	 * Format pace
	 * @param int $secondsPerKm
	 * @return string
	 */
	public function format($secondsPerKm)
	{
		if ($secondsPerKm == 0) {
			return '-:--';
		}

		return Duration::format(round($secondsPerKm * $this->factorForUnit()));
	}

	/**
	 * Compare two paces
	 * 
	 * This value is positive if $firstPaceInSecondsPerKm is larger than $secondPaceInSecondsPerKm
	 * 
	 * @param int $firstPaceInSecondsPerKm
	 * @param int $secondPaceInSecondsPerKm
	 * @return float [s/km]
	 */
	public function compare($firstPaceInSecondsPerKm, $secondPaceInSecondsPerKm)
	{
		return ($secondPaceInSecondsPerKm - $firstPaceInSecondsPerKm);
	}
}