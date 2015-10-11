<?php
/**
 * This file contains the class::SummaryTableAllYears
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */

use Runalyze\Activity\Distance;
use Runalyze\Configuration;

/**
 * Summary table for dataset/data browser
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */
class SummaryTableAllYears extends SummaryTable {
	/**
	 * Prepare summary
	 */
	protected function prepare() {
		$this->Title = __('All years');
		$this->Timerange = 366*DAY_IN_S;
		$this->TimeEnd = mktime(23, 59, 59, 12, 31, date('Y'));
		$this->TimeStart = mktime(0, 0, 1, 1, 1, date('Y', START_TIME));
		$this->AdditionalColumns = 1 * ($this->Sportid == Configuration::General()->runningSport());
	}

	/**
	 * Head for row
	 * @param int $index
	 * @return string
	 */
	protected function rowHead($index) {
		$year = date('Y', $this->TimeEnd - ($index + 0.5)*366*DAY_IN_S);
		$start = mktime(0, 0, 1, 1, 1, $year);
		$end   = mktime(23, 59, 59, 12, 31, $year);

		return DataBrowserLinker::link($year, $start, $end, '');
	}

	/**
	 * Display additional columns
	 * @param array $data
	 */
	protected function displayAdditionalColumns($data) {
		if ($this->AdditionalColumns) {
			$weekFactor  = 52;
			$monthFactor = 12;

			if ($data['timerange'] == 0) {
				$weekFactor  = (date('z')+1) / 7;
				$monthFactor = (date('z')+1) / 30.4;
			} elseif ($data['timerange'] == (date('Y') - START_YEAR) && date('Y', START_TIME) == START_YEAR) {
				$weekFactor  = 53 - date("W", START_TIME);
				$monthFactor = 13 - date("n", START_TIME);
			}

			if ($data['distance'] > 0) {
				$weekString = Distance::format($data['distance']/$weekFactor, true, 0).__('/week');
				$monthString = Distance::format($data['distance']/$monthFactor, true, 0).__('/month');
			} else {
				$weekString = NBSP;
				$monthString = NBSP;
			}

			echo '<td class="small">'.$weekString.'<br>'.$monthString.'</td>';
		}
	}
}
