<?php
/**
 * This file contains the class of the RunalyzePluginStat "Laufabc".
 */
$PLUGINKEY = 'RunalyzePluginStat_Laufabc';
/**
 * Class: RunalyzePluginStat_Laufabc
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class RunalyzePluginStat_Laufabc extends PluginStat {
	private $ABCData = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Lauf-ABC';
		$this->description = 'Wie oft hast du Lauf-ABC absolviert?';
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('F&uuml;r jedes Training kann angegeben werden, ob dabei etwas Lauf-ABC gemacht wurde.
					Wie oft das der Fall war, kann mit diesem Plugin ausgewertet werden.');
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
		$this->displayHeader('Lauf-ABC');
		$this->displayData();
	}

	/**
	 * Display the table with summed data for every month 
	 */
	private function displayData() {
		echo '<table class="fullWidth small r">';
		echo HTML::monthTr(8, 1);
		echo HTML::spaceTR(13);
		
		if (empty($this->ABCData))
			echo '<tr><td colspan="12"><em>Keine Daten gefunden.</em></td></tr>';
		foreach ($this->ABCData as $y => $Data) {
			echo('
				<tr class="a'.($y%2+1).'">
					<td class="b l">'.$y.'</td>'.NL);

			for ($m = 1; $m <= 12; $m++) {
				if (isset($Data[$m]) && $Data[$m]['num'] > 0)
					echo '<td title="'.$Data[$m]['num'].'x">'.round(100*$Data[$m]['abc']/$Data[$m]['num']).' &#37;</td>'.NL;
				else
					echo HTML::emptyTD();
			}

			echo '</tr>'.NL;
		}

		echo HTML::spaceTR(13);
		echo '</table>';
	}

	/**
	 * Initialize $this->ABCData
	 */
	private function initData() {
		$result = Mysql::getInstance()->fetchAsArray('
			SELECT
				SUM(`abc`) as `abc`,
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