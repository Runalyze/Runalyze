<?php
/**
 * This file contains class::KmPerHour
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

/**
 * Pace unit: km/h
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
class KmPerHour extends AbstractDecimalUnit
{
	/**
	 * Unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function unit()
	{
		return \Runalyze\Parameter\Application\PaceUnit::KM_PER_H;
	}

	/**
	 * Appendix
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function appendix()
	{
		return '&nbsp;km/h';
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
		return 3600;
	}
}