<?php
/**
 * This file contains the class::SummaryTable10Weeks
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */

use Runalyze\Util\LocalTime;

/**
 * Summary table for dataset/data browser
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */
class SummaryTable10Weeks extends SummaryTable {
	/**
	 * Prepare summary
	 */
	protected function prepare() {
		$this->Title = __('Last 10 training weeks');
		$this->Timerange = 7*DAY_IN_S;
		$this->TimeEnd = (new LocalTime)->weekend();
		$this->TimeStart = $this->TimeEnd - 10*$this->Timerange;
	}

	/**
	 * Head for row
	 * @param int $index
	 * @return string
	 */
	protected function rowHead($index) {
		$time  = $this->TimeEnd - ($index + 0.5)*7*DAY_IN_S;
		$start = (new LocalTime($time))->weekstart(true);
		$end   = (new LocalTime($time))->weekend(true);
		$week  = Icon::$CALENDAR.' '.__('Week').' '.$start->format('W');

		return DataBrowserLinker::link($week, $start->getTimestamp(), $end->getTimestamp(), '').'</span>&nbsp;&nbsp;&nbsp;<span class="small">'.$start->format('d.m').' - '.$end->format('d.m');
	}
}
