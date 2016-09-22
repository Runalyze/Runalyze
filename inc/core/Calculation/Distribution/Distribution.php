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
	const NUM = 'num';

	/** @var string */
	const MIN = 'min';

	/** @var string */
	const MAX = 'max';

	/** @var string */
	const MEAN = 'mean';

	/** @var string */
	const MEDIAN = 'median';

	/** @var string */
	const QUANTILES = 'quantiles';

	/** @var string */
	const MODE = 'mode';

	/** @var string */
	const VARIANCE = 'var';

	/**
	 * Statistic
	 * @var array
	 */
	private $Statistic = array(
		'num' => 0,
		'min' => 0,
		'max' => 0,
		'mean' => 0,
		'median' => 0,
		'quantiles' => [],
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
	 * @param float[] $quantiles
	 */
	public function calculateStatistic(array $quantiles = []) {
		$this->calculateStatisticByHistogram($quantiles);
	}

	/**
	 * Calculate statistic based on histogram
	 * @param float[] $quantiles
	 */
	final protected function calculateStatisticByHistogram(array $quantiles) {
		$sortedData = $this->histogram();

		if (empty($sortedData)) {
			return;
		}

		ksort($sortedData);

		$this->calculateMinAndMax($sortedData);
		$this->calculateMeanAndMode($sortedData);
		$this->calculateQuantilesAndVariance($sortedData, $quantiles);
	}

	/**
	 * @param array $sortedData
	 */
	private function calculateMinAndMax(array $sortedData)
	{
		$keys = array_keys($sortedData);

		$this->setStatistic(self::MIN, $keys[0]);
		$this->setStatistic(self::MAX, end($keys));
	}

	/**
	 * @param array $sortedData
	 */
	private function calculateMeanAndMode(array $sortedData)
	{
		$sum = 0;
		$num = 0;
		$maxCount = 0;
		$mode = 0;

		foreach ($sortedData as $value => $count) {
			$sum += $value * $count;
			$num += $count;

			if ($count > $maxCount) {
				$maxCount = $count;
				$mode = $value;
			}
		}

		$mean = $sum / $num;
		$this->setStatistic(self::NUM, $num);
		$this->setStatistic(self::MEAN, $mean);
		$this->setStatistic(self::MODE, $mode);
	}

	/**
	 * @param array $sortedData
	 * @param float[] $quantiles
	 */
	private function calculateQuantilesAndVariance(array $sortedData, array $quantiles)
	{
		ksort($quantiles);

		$num = $this->Statistic[self::NUM];
		$currentQuantilesIndex = 0;
		$currentQuantile = array_shift($quantiles);
		$desiredQuantileIndex = null !== $currentQuantile ? $currentQuantile * $num : $num + 1;
		$desiredMedianIndex = $num / 2;

		$mean = $this->Statistic[self::MEAN];
		$median = false;
		$var = 0;

		foreach ($sortedData as $value => $count) {
			$var += $count * ($value - $mean) * ($value - $mean);
			$currentQuantilesIndex += $count;

			if ($median === false && $currentQuantilesIndex >= $desiredMedianIndex) {
				$median = $value;
			}

			while (null !== $currentQuantile && $currentQuantilesIndex >= $desiredQuantileIndex) {
				$this->Statistic[self::QUANTILES]['p'.$currentQuantile] = $value;

				$currentQuantile = array_shift($quantiles);
				$desiredQuantileIndex = null !== $currentQuantile ? $currentQuantile * $num : $num + 1;
			}
		}

		$this->setStatistic(self::MEDIAN, $median);
		$this->setStatistic(self::VARIANCE, $var / $this->Statistic[self::NUM]);
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
	 * @param float $alpha
	 * @return float
	 *
	 * @throws \InvalidArgumentException
	 */
	final public function quantile($alpha) {
		if (!isset($this->Statistic[self::QUANTILES]['p'.$alpha])) {
			throw new \InvalidArgumentException('No quantile calculated for alpha = '.$alpha);
		}

		return $this->Statistic[self::QUANTILES]['p'.$alpha];
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

	/**
	 * Coefficient of variation
	 * @return bool|float boolean false is returned if mean is zero
	 */
	final public function coefficientOfVariation() {
		if ($this->Statistic[self::MEAN] == 0) {
			return false;
		}

		return sqrt($this->Statistic[self::VARIANCE]) / $this->Statistic[self::MEAN];
	}
}
