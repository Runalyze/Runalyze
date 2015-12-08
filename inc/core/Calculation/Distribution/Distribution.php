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
	/** @var string */
	const MIN = 'min';

	/** @var string */
	const MAX = 'max';

	/** @var string */
	const MEAN = 'mean';

	/** @var string */
	const MEDIAN = 'median';

	/** @var string */
	const MODE = 'mode';

	/** @var string */
	const VARIANCE = 'var';

	/**
	 * Statistic
	 * @var array
	 */
	private $Statistic = array(
		'min' => 0,
		'max' => 0,
		'mean' => 0,
		'median' => 0,
		'mode' => 0,
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
		$maxCount = 0;
		$mode = 0;
		foreach ($data as $value => $count) {
			$sum += $value * $count;
			$num += $count;

			if ($count > $maxCount) {
				$maxCount = $count;
				$mode = $value;
			}
		}

		$mean = $sum / $num;
		$this->setStatistic(self::MEAN, $mean);
		$this->setStatistic(self::MODE, $mode);

		$desiredMedianIndex = $num / 2;
		$currentMedianIndex = 0;
		$median = false;
		$var = 0;
		foreach ($data as $value => $count) {
			$var += $count * ($value - $mean) * ($value - $mean);
			$currentMedianIndex += $count;

			if ($median === false && $currentMedianIndex >= $desiredMedianIndex) {
				$median = $value;
			}
		}

		$this->setStatistic(self::MEDIAN, $median);
		$this->setStatistic(self::VARIANCE, $var / $num);
	}

	/**
	 * Set statistic
	 * @param string $key
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
	 * Median
	 * @return float
	 */
	final public function median() {
		return $this->Statistic[self::MEDIAN];
	}

	/**
	 * Mode
	 * @return float
	 */
	final public function mode() {
		return $this->Statistic[self::MODE];
	}

	/**
	 * Variance
	 * @return float
	 */
	final public function variance() {
		return $this->Statistic[self::VARIANCE];
	}

	/**
	 * Standard deviation
	 * @return float
	 */
	final public function stdDev() {
		return sqrt($this->Statistic[self::VARIANCE]);
	}
}