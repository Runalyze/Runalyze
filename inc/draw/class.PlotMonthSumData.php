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
		if ($this->Sport->usesDistance())
			return __('Monthly kilometers').' '.$this->Year;

		return __('Hours of training').' '.$this->Year;
	}

	/**
	 * Get X labels
	 * @return array
	 */
	protected function getXLabels() {
		$months = array();

		for ($m = $this->timerStart; $m <= $this->timerEnd; $m++)
			$months[] = array($m-1, Time::Month($m, true));

		return $months;
	}

	/**
	 * Timer table for query
	 * @return string
	 */
	protected function timer() {
		return 'MONTH(FROM_UNIXTIME(`time`))';
	}
}