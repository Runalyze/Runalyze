<?php
/**
 * This file contains the class::SummaryTableAllWeeks
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */

use Runalyze\Util\LocalTime;

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
		$this->TimeEnd = ($this->Year == date("Y")) ? (new LocalTime)->weekend() : LocalTime::fromString('31.12.'.$this->Year.' 01:00:00')->weekend();
		$this->TimeStart = LocalTime::fromString('01.01.'.$this->Year.' 01:00:00')->weekstart();
	}
}
