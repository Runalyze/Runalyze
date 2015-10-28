<?php
/**
 * This file contains class::MilesPerHour
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

use Runalyze\Parameter\Application\DistanceUnitSystem;

/**
 * Pace unit: mph
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
class MilesPerHour extends AbstractDecimalUnit
{
	/**
	 * Unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function unit()
	{
		return \Runalyze\Parameter\Application\PaceUnit::MILES_PER_H;
	}

	/**
	 * Appendix
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function appendix()
	{
		return '&nbsp;mph';
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
		return 3600 * DistanceUnitSystem::MILE_MULTIPLIER;
	}
}