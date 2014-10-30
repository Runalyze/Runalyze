<?php
/**
 * This file contains class::Distribution
 * @package Runalyze\Calculation\Distribution
 */

namespace Runalyze\Calculation\Distribution;

/**
 * Distribution
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Distribution
 */
abstract class Distribution {
	/**
	 * Minimum
	 */
	const MIN = 'min';

	/**
	 * Maximum
	 */
	const MAX = 'max';

	/**
	 * Mean
	 */
	const MEAN = 'mean';

	/**
	 * Variance
	 */
	const VARIANCE = 'var';

	/**
	 * Statistic
	 * @var array
	 */
	private $Statistic = array(
		'min' => 0,
		'max' => 0,
		'mean' => 0,
		'var' => 0
	);

	/**
	 * Histogram data
	 * @return array
	 */
	abstract public function histogram();

	/**
	 * Calculate statistic
	 */
	public function calculateStatistic() {
		$this->calculateStatisticByHistogram();
	}

	/**
	 * Calculate statistic based on histogram
	 */
	final protected function calculateStatisticByHistogram() {
		$data = $this->histogram();

		ksort($data);

		$keys = array_keys($data);

		$this->setStatistic(self::MIN, $keys[0]);
		$this->setStatistic(self::MAX, end($keys));

		$sum = 0;
		$num = 0;
		foreach ($data as $value => $count) {
			$sum += $value * $count;
			$num += $count;
		}

		$mean = $sum / $num;
		$this->setStatistic(self::MEAN, $mean);

		$var = 0;
		foreach ($data as $value => $count) {
			$var += $count * ($value - $mean) * ($value - $mean);
		}

		$this->setStatistic(self::VARIANCE, $var / $num);
	}

	/**
	 * Set statistic
	 * @param const $key
	 * @param float $value
	 */
	final protected function setStatistic($key, $value) {
		$this->Statistic[$key] = $value;
	}

	/**
	 * Minimum
	 * @return float
	 */
	final public function min() {
		return $this->Statistic[self::MIN];
	}

	/**
	 * Maximum
	 * @return float
	 */
	final public function max() {
		return $this->Statistic[self::MAX];
	}

	/**
	 * Mean
	 * @return float
	 */
	final public function mean() {
		return $this->Statistic[self::MEAN];
	}

	/**
	 * Variance
	 * @return float
	 */
	final public function variance() {
		return $this->Statistic[self::VARIANCE];
	}
}