<?php
/**
 * This file contains class::MinPer500m
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

/**
 * Pace unit: min/500m
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
class MinPer500m extends AbstractTimeUnit
{
	/**
	 * Unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function unit()
	{
		return \Runalyze\Parameter\Application\PaceUnit::MIN_PER_500M;
	}

	/**
	 * Appendix
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function appendix()
	{
		return '/500m';
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
		return 0.5;
	}
}