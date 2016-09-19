<?php
/**
 * This file contains class::TimeSeries
 * @package Runalyze\Calculation\Distribution
 */

namespace Runalyze\Calculation\Distribution;

/**
 * Empirical distribution for time series
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Distribution
 */
class TimeSeries extends Empirical
{
	/**
	 * Construct empirical distribution
	 * @param array $data array of data points
	 * @param array $time continuous time points
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $data, array $time)
	{
		if (empty($time)) {
			throw new \InvalidArgumentException('Time array must not be empty.');
		} elseif (count($time) < count($data)) {
			throw new \InvalidArgumentException('Time array must be at least as large as data array.');
		}

		$lastTime = 0;
		foreach ($data as $i => $val) {
			if (!isset($this->Histogram[$val])) {
				$this->Histogram[$val] = $time[$i] - $lastTime;
			} else {
				$this->Histogram[$val] += $time[$i] - $lastTime;
			}

			$lastTime = $time[$i];
		}
	}
}
