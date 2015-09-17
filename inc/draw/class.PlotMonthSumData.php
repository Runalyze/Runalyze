<?php
/**
 * This file contains class::PlotMonthSumData
 * @package Runalyze\Plot
 */

use Runalyze\Util\Time;

/**
 * Plot month data
 * @package Runalyze\Plot
 */
class PlotMonthSumData extends PlotSumData {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->timerStart = Request::param('y') == parent::LAST_6_MONTHS ? 7 : 1;
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
		return __('Monthly chart:');
	}

	/**
	 * Get X labels
	 * @return array
	 */
	protected function getXLabels() {
		$months = array();
		$add = ($this->Year == parent::LAST_6_MONTHS || $this->Year == parent::LAST_12_MONTHS) ? date('m') : 0;
		$i = 0;

		for ($m = $this->timerStart; $m <= $this->timerEnd; $m++) {
			$months[] = array($i, Time::Month($m + $add, true));
			$i++;
		}

		return $months;
	}

	/**
	 * Timer table for query
	 * @return string
	 */
	protected function timer() {
		if ($this->Year == parent::LAST_6_MONTHS) {
			return '((MONTH(FROM_UNIXTIME(`time`)) + 12 - '.date('m').' - 1)%12 + 1)';
		} elseif ($this->Year == parent::LAST_12_MONTHS) {
			return '((MONTH(FROM_UNIXTIME(`time`)) + 12 - '.date('m').' - 1)%12 + 1)';
		}

		return 'MONTH(FROM_UNIXTIME(`time`))';
	}

	/**
	 * @return int
	 */
	protected function beginningOfLast6Months() {
		return strtotime("first day of -5 months");
	}

	/**
	 * @return int
	 */
	protected function beginningOfLast12Months() {
		return strtotime("first day of -11 months");
	}

	/**
	 * @return float
	 */
	protected function factorForWeekKm() {
		return 365/12/7;
	}
}