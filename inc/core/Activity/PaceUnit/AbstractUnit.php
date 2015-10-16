<?php
/**
 * This file contains class::AbstractUnit
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

/**
 * Abstract pace unit
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
abstract class AbstractUnit
{
	/**
	 * Unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	abstract public function unit();

	/**
	 * Appendix
	 * @return string
	 * @codeCoverageIgnore
	 */
	abstract public function appendix();

	/**
	 * Pace is displayed as time per distance
	 * @return bool
	 */
	abstract public function isTimeFormat();

	/**
	 * Pace is displayed in decimal format
	 * @return bool
	 */
	final public function isDecimalFormat()
	{
		return !$this->isTimeFormat();
	}

	/**
	 * Factor to transform 's/km' to current unit
	 * 
	 * This should only be used for time formats.
	 * 's/km' * factorForUnit() = [time in seconds per distance]
	 * 
	 * @return float
	 * @throws \RuntimeException
	 */
	abstract public function factorForUnit();

	/**
	 * Dividend to transform 's/km' to current unit
	 * 
	 * This should only be used for decimal formats.
	 * dividendForUnit() / 's/km' = [decimal pace value]
	 * 
	 * @return float
	 * @throws \RuntimeException
	 */
	abstract public function dividendForUnit();

	/**
	 * Get raw value
	 * 
	 * This returns for example a float for decimal units and an integer as 's/km'
	 * for time units.
	 * 
	 * @param int $secondsPerKm
	 * @return mixed
	 */
	abstract public function rawValue($secondsPerKm);

	/**
	 * Format pace
	 * @param int $secondsPerKm
	 * @return string
	 */
	abstract public function format($secondsPerKm);

	/**
	 * Compare two paces
	 * 
	 * This value is positive if $firstPaceInSecondsPerKm is larger than $secondPaceInSecondsPerKm
	 * 
	 * @param int $firstPaceInSecondsPerKm
	 * @param int $secondPaceInSecondsPerKm
	 * @return float [s/km]
	 */
	abstract public function compare($firstPaceInSecondsPerKm, $secondPaceInSecondsPerKm);
}