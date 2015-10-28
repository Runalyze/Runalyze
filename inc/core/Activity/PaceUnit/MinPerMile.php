<?php
/**
 * This file contains class::MinPerMile
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

use Runalyze\Parameter\Application\DistanceUnitSystem;

/**
 * Pace unit: min/mi
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
class MinPerMile extends AbstractTimeUnit
{
	/**
	 * Unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function unit()
	{
		return \Runalyze\Parameter\Application\PaceUnit::MIN_PER_MILE;
	}

	/**
	 * Appendix
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function appendix()
	{
		return '/mi';
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
		return 1 / DistanceUnitSystem::MILE_MULTIPLIER;
	}
}