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
		$this->timerStart = 1;
		$this->timerEnd   = date("W", mktime(0,0,0,12,28,(int)$this->Year)); // http://de.php.net/manual/en/function.date.php#49457

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
		if ($this->Sport->usesDistance())
			return 'Wochenkilometer '.$this->Year;

		return 'Trainingsstunden '.$this->Year;
	}

	/**
	 * Get X labels
	 * @return array
	 */
	protected function getXLabels() {
		$weeks = array();

		for ($w = $this->timerStart; $w <= $this->timerEnd; $w++)
			$weeks[] = array($w-$this->timerStart, ($w%5 == 0) ? $w : '');

		return $weeks;
	}

	/**
	 * Timer table for query
	 * @return string
	 */
	protected function timer() {
		return 'WEEK(FROM_UNIXTIME(`time`),1)';
	}
}