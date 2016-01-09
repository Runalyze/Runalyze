<?php
/**
 * This file contains class::TimeSeriesForTrackdata
 * @package Runalyze\Calculation\Distribution
 */

namespace Runalyze\Calculation\Distribution;

use Runalyze\Model\Trackdata;

/**
 * Empirical distribution for time series for trackdata
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Distribution
 */
class TimeSeriesForTrackdata extends TimeSeries {
	/**
	 * @var \Runalyze\Model\Trackdata\Entity 
	 */
	protected $Trackdata;

	/**
	 * @var int enum
	 */
	protected $IndexKey;

	/**
	 * @var int[] enums
	 */
	protected $SumDifferencesKeys;

	/**
	 * @var int[] enums
	 */
	protected $AvgValuesKeys;

	/**
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Construct time series for trackdata object
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param int $indexKey enum
	 * @param int[] $sumDifferencesKeys enums
	 * @param int[] $avgValuesKeys enums
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		Trackdata\Entity $trackdata,
		$indexKey,
		$sumDifferencesKeys = array(),
		$avgValuesKeys = array()
	) {
		$this->Trackdata = $trackdata;
		$this->IndexKey = $indexKey;
		$this->SumDifferencesKeys = $sumDifferencesKeys;
		$this->AvgValuesKeys = $avgValuesKeys;

		foreach (array_merge($sumDifferencesKeys, $avgValuesKeys) as $key) {
			if (!$trackdata->has($key)) {
				$trackdata->set($key, array_fill(0, $trackdata->num(), 0));
			}
		}

		parent::__construct($trackdata->get($indexKey), $trackdata->get(Trackdata\Entity::TIME));
		$this->collectData();
	}

	/**
	 * Collect data
	 */
	protected function collectData() {
		$data = $this->Trackdata->get($this->IndexKey);
		$time = $this->Trackdata->get(Trackdata\Entity::TIME);
		$rawdata = array();

		foreach (array_merge($this->SumDifferencesKeys, $this->AvgValuesKeys) as $key) {
			$rawdata[$key] = $this->Trackdata->get($key);
		}

		foreach ($data as $i => $val) {
			if (!isset($this->Data[$val])) {
				$this->Data[$val] = array();

				foreach (array_merge($this->SumDifferencesKeys, $this->AvgValuesKeys) as $key) {
					$this->Data[$val][$key] = 0;
				}
			}

			foreach ($this->SumDifferencesKeys as $key) {
				$prev = $i == 0 ? 0 : $rawdata[$key][$i-1];
				$this->Data[$val][$key] += $rawdata[$key][$i] - $prev;
			}

			$timeDiff = $i == 0 ? $time[0] : $time[$i] - $time[$i-1];

			foreach ($this->AvgValuesKeys as $key) {
				$this->Data[$val][$key] += $rawdata[$key][$i] * $timeDiff;
			}
		}

		$this->calculateAverages();
	}

	/**
	 * Calculate averages
	 */
	protected function calculateAverages() {
		if (!empty($this->AvgValuesKeys)) {
			foreach ($this->Data as $index => $data) {
				foreach ($this->AvgValuesKeys as $key) {
					if ($this->Histogram[$index] > 0) {
						$this->Data[$index][$key] = $data[$key] / $this->Histogram[$index];
					}
				}
			}
		}
	}

	/**
	 * @return array[] There is an array for each index of the histogram with data for each requested key
	 */
	public function data() {
		return $this->Data;
	}
}