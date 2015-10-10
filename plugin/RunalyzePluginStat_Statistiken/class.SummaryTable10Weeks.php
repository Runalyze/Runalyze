<?php
/**
 * This file contains the class::SummaryTable10Weeks
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */

use Runalyze\Util\Time;

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
		$this->TimeEnd = Time::weekend(time());
		$this->TimeStart = $this->TimeEnd - 10*$this->Timerange;
	}

	/**
	 * Head for row
	 * @param int $index
	 * @return string
	 */
	protected function rowHead($index) {
		$time  = $this->TimeEnd - ($index + 0.5)*7*DAY_IN_S;
		$start = Time::weekstart($time);
		$end   = Time::weekend($time);
		$week  = Icon::$CALENDAR.' '.__('Week').' '.date('W', $time);

		return DataBrowserLinker::link($week, $start, $end, '').'</span>&nbsp;&nbsp;&nbsp;<span class="small">'.date('d.m', $start).' - '.date('d.m', $end);
	}
}
