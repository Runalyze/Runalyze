<?php
/**
 * This file contains class::MinPerKilometer
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

/**
 * Pace unit: min/km
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
class MinPerKilometer extends AbstractTimeUnit
{
	/**
	 * Unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function unit()
	{
		return \Runalyze\Parameter\Application\PaceUnit::MIN_PER_KM;
	}

	/**
	 * Appendix
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function appendix()
	{
		return '/km';
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
	public function factorForUnit()
	{
		return 1;
	}
}