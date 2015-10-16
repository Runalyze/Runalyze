<?php
/**
 * This file contains class::MeterPerSecond
 * @package Runalyze\Activity\PaceUnit
 */

namespace Runalyze\Activity\PaceUnit;

/**
 * Pace unit: m/s
 * @author Hannes Christiansen
 * @package Runalyze\Activity\PaceUnit
 */
class MeterPerSecond extends AbstractDecimalUnit
{
	/**
	 * Unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function unit()
	{
		return \Runalyze\Parameter\Application\PaceUnit::M_PER_S;
	}

	/**
	 * Appendix
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function appendix()
	{
		return '&nbsp;m/s';
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
		return 1000;
	}
}