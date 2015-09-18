<?php
/**
 * This file contains class::PlotWeekSumData
 * @package Runalyze\Plot
 */

use Runalyze\Util\Time;

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

		if (Request::param('y') == self::LAST_6_MONTHS) {
			$this->timerEnd = 26;
		} else {
			if (Request::param('y') == self::LAST_12_MONTHS) {
				$yearEnd = date('Y') - 1;
			} else {
				$yearEnd = (int)Request::param('y');
			}

			$this->timerEnd = date("W", mktime(0,0,0,12,28,$yearEnd)); // http://de.php.net/manual/en/function.date.php#49457
		}

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
		return __('Weekly chart:');
	}

	/**
	 * Get X labels
	 * @return array
	 */
	protected function getXLabels() {
		$weeks = array();
		$add = ($this->Year == parent::LAST_6_MONTHS || $this->Year == parent::LAST_12_MONTHS) ? 0 : date("W") - $this->timerEnd;

		for ($w = $this->timerStart; $w <= $this->timerEnd; $w++) {
			$time = strtotime("sunday -".($this->timerEnd - $w + $add)." weeks");
			$string = (date("d", $time) <= 7) ? Time::Month(date("m", $time), true) : '';

			if ($string != '' && date("m", $time) == 1) {
				$string .= ' \''.date("y", $time);
			}

			$weeks[] = array($w-$this->timerStart, $string);
		}

		return $weeks;
	}

	/**
	 * Timer table for query
	 * @return string
	 */
	protected function timer() {
		if ($this->Year == parent::LAST_6_MONTHS || $this->Year == parent::LAST_12_MONTHS) {
			return '((WEEK(FROM_UNIXTIME(`time`),1) + '.$this->timerEnd.' - '.date('W').' - 1)%'.$this->timerEnd.' + 1)';
		}

		return 'WEEK(FROM_UNIXTIME(`time`),1)';
	}

	/**
	 * @return int
	 */
	protected function beginningOfLast6Months() {
		return $this->beginningOfTimerange();
	}

	/**
	 * @return int
	 */
	protected function beginningOfLast12Months() {
		return $this->beginningOfTimerange();
	}

	/**
	 * @return int
	 */
	protected function beginningOfTimerange() {
		if (date('w') == 0) {
			return strtotime("monday -".$this->timerEnd." weeks");
		}

		return strtotime("monday -".($this->timerEnd - 1)." weeks");
	}

	/**
	 * @return float
	 */
	protected function factorForWeekKm() {
		return 1;
	}
}