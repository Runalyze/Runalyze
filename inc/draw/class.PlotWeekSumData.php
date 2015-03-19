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

		$this->addAverage();
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

		return __('Weekly chart:').' '.$Year;
	}

	/**
	 * Get X labels
	 * @return array
	 */
	protected function getXLabels() {
		$weeks = array();
		$add = $this->Year == self::LAST_12_MONTHS ? 0 : date("W") - $this->timerEnd;

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

	/**
	 * @return bool
	 */
	protected function showsAverage() {
		return ($this->Sport->isRunning() && $this->Analysis == parent::ANALYSIS_DEFAULT && $this->Year == parent::LAST_12_MONTHS);
	}

	/**
	 * Add line for average and goal
	 */
	protected function addAverage() {
		if ($this->showsAverage()) {
			$BasicEndurance = new BasicEndurance();
			$BasicEndurance->readSettingsFromConfiguration();
			$Result = $BasicEndurance->asArray();

			$Avg = $Result['weekkm-percentage']*$BasicEndurance->getTargetWeekKm();
			$Goal = $BasicEndurance->getTargetWeekKm();

			$this->addThreshold('y', $Avg, '#333');
			$this->addThreshold('y', $Goal, '#ccc');

			$this->addAnnotation(0, $Avg, __('Avg*'), 0, 0);
			$this->addAnnotation(0, $Goal, __('Goal*'), 0, 0);
		}
	}

	/**
	 * Display additional info
	 */
	protected function displayInfos() {
		if ($this->showsAverage()) {
			$BasicEndurance = new BasicEndurance();
			$BasicEndurance->readSettingsFromConfiguration();

			echo HTML::info(
				'* ' . sprintf(
					__('These lines belong to our calculations of your basic endurance. '.
						'The average is taken over %s weeks and the goal belongs to preparing an optimal marathon.'),
					round($BasicEndurance->getDaysForWeekKm() / 7)
				)
			);
		}
	}
}