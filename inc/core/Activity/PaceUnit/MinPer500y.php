<?php
/**
 * This file contains class::MinPer500y
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

use Runalyze\Parameter\Application\DistanceUnitSystem;

/**
 * Pace unit: min/500y
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
class MinPer500y extends AbstractTimeUnit
{
	/**
	 * Unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function unit()
	{
		return \Runalyze\Parameter\Application\PaceUnit::MIN_PER_500Y;
	}

	/**
	 * Appendix
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function appendix()
	{
		return '/500y';
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
		return 500 / DistanceUnitSystem::YARD_MULTIPLIER;
	}
}