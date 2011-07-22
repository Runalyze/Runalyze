<?php
/**
 * This file contains the class of the RunalyzePlugin "LaufabcStat".
 */
$PLUGINKEY = 'RunalyzePlugin_LaufabcStat';
/**
 * Class: RunalyzePlugin_LaufabcStat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Helper
 *
 * Last modified 2011/07/10 13:00 by Hannes Christiansen
 */
class RunalyzePlugin_LaufabcStat extends PluginStat {
	private $ABCData = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Lauf-ABC';
		$this->description = 'Wie oft hast du Lauf-ABC absolviert?';

		$this->initData();
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayHeader('Lauf-ABC');
		$this->displayData();
	}

	/**
	 * Display the table with summed data for every month 
	 */
	private function displayData() {
		echo '<table style="width:100%;" class="small">';
		echo Helper::monthTr(8, 1);
		echo Helper::spaceTR(13);
		
		if (empty($this->ABCData))
			echo '<tr><td colspan="12"><em>Keine Daten gefunden.</em></td></tr>';
		foreach ($this->ABCData as $y => $Data) {
			echo('
				<tr class="a'.($y%2+1).' r">
					<td class="b l">'.$y.'</td>'.NL);

			for ($m = 1; $m <= 12; $m++) {
				if ($Data[$m]['num'] > 0)
					echo '<td title="'.$Data[$m]['num'].'x">'.round(100*$Data[$m]['abc']/$Data[$m]['num']).' &#37;</td>'.NL;
				else
					echo Helper::emptyTD();
			}

			echo '</tr>'.NL;
		}

		echo Helper::spaceTR(13);
		echo '</table>';
	}

	/**
	 * Initialize $this->ABCData
	 */
	private function initData() {
		$result = Mysql::getInstance()->fetchAsArray('
			SELECT
				SUM(`laufabc`) as `abc`,
				SUM(1) as `num`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`
			FROM `'.PREFIX.'training`
			GROUP BY `year`, `month`');
		
		foreach ($result as $dat) {
			if ($dat['abc'] > 0)
				$this->ABCData[$dat['year']][$dat['month']] = array('abc' => $dat['abc'], 'num' => $dat['num']);
		}
	}
}
?>