<?php
/**
 * This file contains class::PlotWeekSumData
 * @package Runalyze\Plot
 */
/**
 * Plot week data
 * @package Runalyze\Plot
 */
class PlotWeekSumData extends PlotSumData {
	/**
	 * Constructor
	 */
	public function __construct() {
		$yearEnd = Request::param('y') == self::LAST_12_MONTHS ? date('Y')-1 : (int)Request::param('y');
		$this->timerStart = 1;
		$this->timerEnd   = date("W", mktime(0,0,0,12,28,$yearEnd)); // http://de.php.net/manual/en/function.date.php#49457

		parent::__construct();
	}

	/**
	 * Get CSS id
	 * @return string
	 */
	protected function getCSSid() {
		return 'weekKM'.$this->Year.'_'.$this->Sport->id();
	}

	/**
	 * Get title
	 * @return string
	 */
	protected function getTitle() {
		$Year = $this->Year == parent::LAST_12_MONTHS ? __('last 12 months') : $this->Year;

		if ($this->Sport->usesDistance())
			return __('Weekly kilometers').' '.$Year;

		return __('Hours of training').' '.$Year;
	}

	/**
	 * Get X labels
	 * @return array
	 */
	protected function getXLabels() {
		$weeks = array();
		$add = $this->Year == self::LAST_12_MONTHS ? date('W') : 0;

		for ($w = $this->timerStart; $w <= $this->timerEnd; $w++)
			$weeks[] = array($w-$this->timerStart, (($w+$add)%5 == 0) ? ($w + $add)%$this->timerEnd : '');

		return $weeks;
	}

	/**
	 * Timer table for query
	 * @return string
	 */
	protected function timer() {
		if ($this->Year == parent::LAST_12_MONTHS) {
			return '((WEEK(FROM_UNIXTIME(`time`),1) + '.$this->timerEnd.' - '.date('W').' - 1)%'.$this->timerEnd.' + 1)';
		}

		return 'WEEK(FROM_UNIXTIME(`time`),1)';
	}

	/**
	 * @return int
	 */
	protected function beginningOfLast12Months() {
		return strtotime("monday -".$this->timerEnd." weeks");
	}
}