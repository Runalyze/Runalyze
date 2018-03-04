<?php
/**
 * This file contains the class::SummaryTableAllYears
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */

use Runalyze\Activity\Distance;
use Runalyze\Configuration;
use Runalyze\Util\LocalTime;

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
		$this->TimeEnd = (new LocalTime)->yearEnd();
		$this->TimeStart = (new LocalTime(START_TIME))->yearStart();
		$this->AdditionalColumns = 1 * ($this->Sportid == Configuration::General()->runningSport());
	}

	/**
	 * Head for row
	 * @param int $index
	 * @return string
	 */
	protected function rowHead($index) {
		$timestampInMiddelOfYear = $this->TimeEnd - ($index + 0.5)*366*DAY_IN_S;

		return DataBrowserLinker::yearLink(date('Y', $timestampInMiddelOfYear), $timestampInMiddelOfYear);
	}

	/**
	 * Display additional columns
	 * @param array $data
	 */
	protected function displayAdditionalColumns($data) {
		if ($this->AdditionalColumns) {
			$startTime = new LocalTime(START_TIME);

			if ((int)$data['timerange'] == 0) {
				$now = new LocalTime();
				$weekFactor  = ((int)$now->format('z') + 1) / 7;
				$monthFactor = ((int)$now->format('z') + 1) / 30.4;
			} else {
                $numberOfDaysInYear = date('z', mktime(0, 0, 0, 12, 31, (int)$data['timerange'] + START_YEAR)) + 1;

                if ((int)$data['timerange'] == (date('Y') - START_YEAR) && $startTime->format('Y') == START_YEAR) {
                    $weekFactor = ($numberOfDaysInYear - (int)$startTime->format('z')) / 7;
                    $monthFactor = ($numberOfDaysInYear - (int)$startTime->format('z')) / 30.4;
                } else {
                    $weekFactor = $numberOfDaysInYear / 7.0;
                    $monthFactor = 12;
                }
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
