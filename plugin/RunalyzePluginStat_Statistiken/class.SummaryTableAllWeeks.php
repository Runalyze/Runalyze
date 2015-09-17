<?php
/**
 * This file contains the class::SummaryTableAllWeeks
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */

use Runalyze\Util\Time;

/**
 * Summary table for dataset/data browser
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */
class SummaryTableAllWeeks extends SummaryTable10Weeks {
	/**
	 * Prepare summary
	 */
	protected function prepare() {
		parent::prepare();

		$this->Title = sprintf(__('All training weeks %s'), $this->Year);
		$this->TimeEnd = ($this->Year == date("Y")) ? Time::Weekend(time()) : Time::Weekend(mktime(1, 0, 0, 12, 31, $this->Year));
		$this->TimeStart = Time::Weekstart(mktime(1, 0, 0, 12, 31, $this->Year-1));
	}
}
