<?php
/**
 * This file contains the class of the RunalyzePluginStat "Laufabc".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Laufabc';

use Runalyze\Configuration;

/**
 * Class: RunalyzePluginStat_Laufabc
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Laufabc extends PluginStat {
	private $ABCData = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Running Drills');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('How often did you do your running drills?');
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->initData();
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayData();
	}

	/**
	 * Display the table with summed data for every month 
	 */
	private function displayData() {
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>'.HTML::monthTr(8, 1).'</thead>';
		echo '<tbody>';

		if (empty($this->ABCData)) {
			echo '<tr><td colspan="13" class="c"><em>'.__('No activities with running drills found.').'</em></td></tr>';
		}

		foreach ($this->ABCData as $y => $Data) {
			echo '<tr><td class="b l">'.$y.'</td>';

			for ($m = 1; $m <= 12; $m++) {
				if (isset($Data[$m]) && $Data[$m]['num'] > 0) {
					echo '<td title="'.$Data[$m]['num'].'x">'.round(100*$Data[$m]['abc']/$Data[$m]['num']).' &#37;</td>';
				} else {
					echo HTML::emptyTD();
				}
			}

			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Initialize $this->ABCData
	 */
	private function initData() {
		$result = DB::getInstance()->query('
			SELECT
				SUM(`abc`) as `abc`,
				SUM(1) as `num`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`
			FROM `'.PREFIX.'training`
				WHERE `sportid`='.Configuration::General()->runningSport().' AND `accountid`='.SessionAccountHandler::getId().'
			GROUP BY `year` DESC, `month` ASC'
		)->fetchAll();
		
		foreach ($result as $dat) {
			if ($dat['abc'] > 0) {
				$this->ABCData[$dat['year']][$dat['month']] = array('abc' => $dat['abc'], 'num' => $dat['num']);
			}
		}
	}
}