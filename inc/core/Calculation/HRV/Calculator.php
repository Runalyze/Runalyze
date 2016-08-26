<?php
/**
 * This file contains class::Calculator
 * @package Runalyze\Calculation\HRV
 */

namespace Runalyze\Calculation\HRV;

use Runalyze\Model\HRV\Entity;

/**
 * Calculate statistics for hrv data
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\HRV
 */
class Calculator {
	/**
	 * @var \Runalyze\Model\HRV\Entity
	 */
	protected $Object;

	/**
	 * 5 min sub intervals
	 * @var array
	 */
	protected $SubIntervals = array();

	/**
	 * @var int
	 */
	protected $CurrentSubIntervalNum = 0;

	/**
	 * @var int
	 */
	protected $CurrentSubIntervalSum = 0;

	/**
	 * Average r-r interval
	 * @var int [ms]
	 */
	protected $Mean = 0;

	/**
	 * Standard deviation of r-r intervals
	 * @var float [ms]
	 */
	protected $SDNN = 0;

	/**
	 * Standard deviation of 5 min averages of r-r intervals
	 * @var float [ms]
	 */
	protected $SDANN = 0;

	/**
	 * Root mean square of successive differences
	 * @var float [ms]
	 */
	protected $RMSSD = 0;

	/**
	 * Standard deviation of successive differences
	 * @var float [ms]
	 */
	protected $SDSD = 0;

	/**
	 * Proportion of successive differences larger than 50ms
	 * @var float
	 */
	protected $pNN50 = 0;

	/**
	 * Number of successive differences larger than 50ms
	 * @var int
	 */
	protected $NN50 = 0;

	/**
	 * Proportion of successive differences larger than 20ms
	 * @var float
	 */
	protected $pNN20 = 0;

	/**
	 * Number of successive differences larger than 20ms
	 * @var int
	 */
	protected $NN20 = 0;

	/**
	 * Percentage of filtered r-r intervals
	 * @var float in [0.0, 1.0]
	 */
	protected $PercentageAnomalies = 0.0;

	/**
	 * Calculator for hrv statistics
	 *
	 * http://www.zhb.uni-luebeck.de/epubs/ediss1118.pdf, 2.4.2.1 suggests a filter threshold of 75 %
	 *
	 * @param \Runalyze\Model\HRV\Entity $hrvObject
	 * @param double|null $filterThreshold RR intervals are considered only if they differ from the preceding or following interval by less than XXX %
	 * @param int $absoluteThreshold RR intervals above this threshold will be ignored (unless $filterThreshold is null)
	 */
	public function __construct(Entity $hrvObject, $filterThreshold = 0.75, $absoluteThreshold = 2000) {
		$this->Object = clone $hrvObject;

		if (null !== $filterThreshold && $this->Object->num() > 0) {
			$this->filterByThreshold($filterThreshold, $absoluteThreshold);
		}
	}

	/**
	 * @return \Runalyze\Model\HRV\Entity
	 */
	public function filteredObject() {
		return $this->Object;
	}

	/**
	 * Remove all rr intervals that are not within [1 - $filterThreshold, 1 + $filterThreshold]-times their preceding/following interval
	 * @param double $filterThreshold
	 * @param int $absoluteThreshold
	 */
	protected function filterByThreshold($filterThreshold, $absoluteThreshold) {
		$oldData = array_values(array_filter($this->Object->data()));
		$newData = [$oldData[0]];
		$num = count($oldData);

		for ($i = 1; $i < $num; ++$i) {
			$ratioPreceding = $oldData[$i] / $oldData[$i-1];
			$ratioFollowing = ($i < $num - 1) ? $oldData[$i] / $oldData[$i+1] : 1;

			if (
				$oldData[$i] < $absoluteThreshold && (
					(max($ratioPreceding, 1/$ratioPreceding) <= 1 + $filterThreshold) ||
					(max($ratioFollowing, 1/$ratioFollowing) <= 1 + $filterThreshold)
				)
			) {
				$newData[] = $oldData[$i];
			}
		}

		$this->PercentageAnomalies = ($num - count($newData))/$num;

		$this->Object->set(Entity::DATA, $newData);
	}

	/**
	 * Average r-r interval
	 * @return int [ms]
	 */
	public function mean() {
		return $this->Mean;
	}

	/**
	 * Standard deviation of r-r intervals
	 * @return float [ms]
	 */
	public function SDNN() {
		return $this->SDNN;
	}

	/**
	 * Standard deviation of 5 min averages of r-r intervals
	 * @return float [ms]
	 */
	public function SDANN() {
		return $this->SDANN;
	}

	/**
	 * Root mean square of successive differences
	 * @return float [ms]
	 */
	public function RMSSD() {
		return $this->RMSSD;
	}

	/**
	 * Standard deviation of successive differences
	 * @return float [ms]
	 */
	public function SDSD() {
		return $this->SDSD;
	}

	/**
	 * Proportion of successive differences larger than 50ms
	 * @return float
	 */
	public function pNN50() {
		return $this->pNN50;
	}

	/**
	 * Proportion of successive differences larger than 20ms
	 * @return float
	 */
	public function pNN20() {
		return $this->pNN20;
	}

	/**
	 * @return float in [0.0, 1.0]
	 */
	public function percentageAnomalies() {
		return $this->PercentageAnomalies;
	}

	/**
	 * Calculate all statistics
	 */
	public function calculate() {
		if ($this->Object->num() < 2) {
			return;
		}

		$sum = 0;
		$sumSquaredDifferences = 0;
		$sumDifferences = 0;

		$this->loopThroughDataAndCalculate($sum, $sumDifferences, $sumSquaredDifferences);

		$this->Mean = $sum / $this->Object->num();
		$this->RMSSD = sqrt($sumSquaredDifferences / ($this->Object->num() - 1));

		$this->calculateSDNNbasedOnMean();
		$this->finishSubIntervalsForSDANN();
		$this->calculateSDSDfromMean($sumDifferences / $this->Object->num() );
		$this->finishProportionStatistics();
	}

	/**
	 * Loop through data and calculate sums
	 * @param int $sum
	 * @param int $sumDifferences
	 * @param int $sumSquaredDifferences
	 */
	protected function loopThroughDataAndCalculate(&$sum, &$sumDifferences, &$sumSquaredDifferences) {
		$last = 0;

		foreach ($this->Object->data() as $ms) {
			if ($last) {
				$diff = abs($ms - $last);
				$sumSquaredDifferences += $diff * $diff;
				$sumDifferences += $diff;

				if ($diff > 50) {
					$this->NN50++;
					$this->NN20++;
				} elseif ($diff > 20) {
					$this->NN20++;
				}
			}

			$sum += $ms;
			$last = $ms;

			$this->handleSubIntervalForSDANN($ms);
		}
	}

	/**
	 * Calculate SDNN based on mean
	 */
	protected function calculateSDNNbasedOnMean() {
		$sum = 0;

		foreach ($this->Object->data() as $ms) {
			$sum += ($ms - $this->Mean) * ($ms - $this->Mean);
		}

		$this->SDNN = sqrt($sum / ($this->Object->num() - 1));
	}

	/**
	 * Handle current sub interval
	 * @param int $ms
	 */
	protected function handleSubIntervalForSDANN($ms) {
		$this->CurrentSubIntervalNum++;
		$this->CurrentSubIntervalSum += $ms;

		if ($this->CurrentSubIntervalSum > 5*60*1000) {
			$this->SubIntervals[] = $this->CurrentSubIntervalSum / $this->CurrentSubIntervalNum;

			$this->CurrentSubIntervalNum = 0;
			$this->CurrentSubIntervalSum = 0;
		}
	}

	/**
	 * Finish sub intervals to calculate SDANN
	 */
	protected function finishSubIntervalsForSDANN() {
		if (empty($this->SubIntervals)) {
			return;
		}

		$count = count($this->SubIntervals);
		$mean = array_sum($this->SubIntervals) / $count;
		$sum = 0;

		foreach ($this->SubIntervals as $x) {
			$sum += ($x - $mean) * ($x - $mean);
		}

		$this->SDANN = $count == 1 ? sqrt($sum) : sqrt($sum / ($count - 1));
	}

	/**
	 * Calculate sdsd from mean of differences
	 * @param float $mean
	 */
	protected function calculateSDSDfromMean($mean) {
		$last = 0;
		$sum = 0;

		foreach ($this->Object->data() as $ms) {
			if ($last) {
				$x = abs($ms - $last);
				$sum += ($x - $mean) * ($x - $mean);
			}

			$last = $ms;
		}

		$this->SDSD = sqrt($sum / ($this->Object->num() - 1));
	}

	/**
	 * Finish proportional statistics
	 */
	protected function finishProportionStatistics() {
		$this->pNN50 = $this->NN50 / $this->Object->num();
		$this->pNN20 = $this->NN20 / $this->Object->num();
	}
}
