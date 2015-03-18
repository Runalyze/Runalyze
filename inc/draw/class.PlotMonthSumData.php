<?php
/**
 * This file contains class::PlotMonthSumData
 * @package Runalyze\Plot
 */
/**
 * Plot month data
 * @package Runalyze\Plot
 */
class PlotMonthSumData extends PlotSumData {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->timerStart = 1;
		$this->timerEnd   = 12;

		parent::__construct();
	}

	/**
	 * Get CSS id
	 * @return string
	 */
	protected function getCSSid() {
		return 'monthKM'.$this->Year.'_'.$this->Sport->id();
	}

	/**
	 * Get title
	 * @return string
	 */
	protected function getTitle() {
		$Year = $this->Year == parent::LAST_12_MONTHS ? __('last 12 months') : $this->Year;

		if ($this->Sport->usesDistance())
			return __('Monthly kilometers').' '.$Year;

		return __('Hours of training').' '.$Year;
	}

	/**
	 * Get X labels
	 * @return array
	 */
	protected function getXLabels() {
		$months = array();
		$add = $this->Year == self::LAST_12_MONTHS ? date('m') : 0;

		for ($m = $this->timerStart; $m <= $this->timerEnd; $m++)
			$months[] = array($m-1, Time::Month($m + $add, true));

		return $months;
	}

	/**
	 * Timer table for query
	 * @return string
	 */
	protected function timer() {
		if ($this->Year == parent::LAST_12_MONTHS) {
			return '((MONTH(FROM_UNIXTIME(`time`)) + 12 - '.date('m').' - 1)%12 + 1)';
		}

		return 'MONTH(FROM_UNIXTIME(`time`))';
	}

	/**
	 * @return int
	 */
	protected function beginningOfLast12Months() {
		return strtotime("first day of -11 months");
	}
}