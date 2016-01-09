<?php
/**
 * This file contains the class::SummaryTableAllYears
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */

use Runalyze\Util\Time;

/**
 * Summary table for dataset/data browser
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */
class SummaryTableMonths extends SummaryTable {
	/**
	 * @var enum
	 */
	const MODE_LAST_6 = 0;

	/**
	 * @var enum
	 */
	const MODE_LAST_12 = 1;

	/**
	 * @var enum
	 */
	const MODE_ALL = 2;

	/**
	 * @var enum
	 */
	const MODE_YEAR = 3;

	/**
	 * @var enum
	 */
	protected $Mode = 0;

	/**
	 * @param enum $mode
	 */
	public function setMode($mode) {
		$this->Mode = $mode;
	}

	/**
	 * Prepare summary
	 */
	protected function prepare() {
		$this->Timerange = 31*DAY_IN_S;

		switch ($this->Mode) {
			case self::MODE_LAST_6:
				$this->Title = __('Last 6 months');
				$this->TimeEnd = mktime(23, 59, 59, date('m')+1, 0, date('Y'));
				$this->TimeStart = mktime(0, 0, 1, date('m')-6, 1, date('Y'));
				break;

			case self::MODE_LAST_12:
				$this->Title = __('Last 12 months');
				$this->TimeEnd = mktime(23, 59, 59, date('m')+1, 0, date('Y'));
				$this->TimeStart = mktime(0, 0, 1, date('m')-12, 1, date('Y'));
				break;

			case self::MODE_ALL:
				$this->Title = __('All months');
				$this->TimeEnd = mktime(23, 59, 59, 12, 31, date('Y'));
				$this->TimeStart = mktime(0, 0, 1, 1, 1, date('Y', START_TIME));
				break;

			case self::MODE_YEAR:
				$this->Title = $this->Year;
				$this->TimeEnd = mktime(23, 59, 59, 12, 31, $this->Year);
				$this->TimeStart = mktime(0, 0, 1, 1, 1, $this->Year);
				break;
		}
	}

	/**
	 * Head for row
	 * @param int $index
	 * @return string
	 */
	protected function rowHead($index) {
		$midOfTimerange = $this->TimeEnd - ($index + 0.5)*31*DAY_IN_S;
		$year = ($this->Mode == self::MODE_YEAR) ? $this->Year : date('Y', $midOfTimerange);
		$month = date('m', $midOfTimerange);
		$start = mktime(0, 0, 1, $month, 1, $year);
		$end   = mktime(23, 59, 59, $month+1, 0, $year);

		return DataBrowserLinker::link(Time::month($month), $start, $end, '');
	}
}
