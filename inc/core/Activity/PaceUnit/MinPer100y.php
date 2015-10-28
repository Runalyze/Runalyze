<?php
/**
 * This file contains class::MinPer100y
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

use Runalyze\Parameter\Application\DistanceUnitSystem;

/**
 * Pace unit: min/100y
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
class MinPer100y extends AbstractTimeUnit
{
	/**
	 * Unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function unit()
	{
		return \Runalyze\Parameter\Application\PaceUnit::MIN_PER_100Y;
	}

	/**
	 * Appendix
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function appendix()
	{
		return '/100y';
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
		return 100 / DistanceUnitSystem::YARD_MULTIPLIER;
	}
}