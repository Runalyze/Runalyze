<?php
/**
 * This file contains the class of the RunalyzePluginStat "Rekorde".
 */
$PLUGINKEY = 'RunalyzePluginStat_Rekorde';
/**
 * Class: RunalyzePluginStat_Rekorde
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Helper
 */
class RunalyzePluginStat_Rekorde extends PluginStat {
	private $rekorde = array();
	private $monts  = array();
	private $weeks  = array();
	private $years  = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Rekorde';
		$this->description = 'Am schnellsten, am l&auml;ngsten, am weitesten: Die Rekorde aus dem Training.';

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
		$this->displayHeader('Rekorde');
		$this->displayRekorde();
		$this->displayMostKilometer();
	}

	/**
	 * Display the table with general records
	 */
	private function displayRekorde() {
		foreach ($this->rekorde as $rekord) {
			echo '<table style="width:100%;" class="small">';
			echo '<tr class="b"><td colspan="11">'.$rekord['name'].'</td></tr>';
			echo HTML::spaceTR(11);

			$output = false;
			eval('$sports = Mysql::getInstance()->fetchAsArray(\''.$rekord['sportquery'].'\');');
			foreach ($sports as $i => $sport) {
				eval('$data = Mysql::getInstance()->fetchAsArray(\''.$rekord['datquery'].'\');');

				if (!empty($data)) {
					$output = true;
					echo '<tr class="a'.($i%2 + 1).' r">';
					echo '<td class="b l">'.Icon::getSportIcon($sport['id']).' '.$sport['name'].'</td>';
	
					$j = 0;
					foreach ($data as $j => $dat) {
						if ($rekord['eval'] == 0)
							$code = Helper::Speed($dat['distance'], $dat['s'], $sport['id']);
						elseif ($rekord['eval'] == 1)
							$code = ($dat['distance'] != 0 ? Helper::Km($dat['distance']) : Helper::Time($dat['s']));
	
						echo('<td><span title="'.date("d.m.Y",$dat['time']).'">
								'.Ajax::trainingLink($dat['id'], $code).'
							</span></td>');
					}
	
					for (; $j < 9; $j++)
						echo HTML::emptyTD();
	
					echo '</tr>';
				}
			}

			if (!$output)
				echo '<tr class="a1"><td colspan="11"><em>Es sind bisher keine Trainingsdaten vorhanden.</em></td></tr>';

			echo HTML::spaceTR(11);
			echo '</table>';
		}
	}

	/**
	 * Display the table with most kilometer for each year/month/week
	 */
	private function displayMostKilometer() {
		echo '<table style="width:100%;" class="small">';
		echo '<tr class="b"><td colspan="11">Trainingsreichsten Laufphasen</td></tr>';
		echo HTML::spaceTR(11);

		if (empty($this->years)) {
			echo '<tr class="a1"><td colspan="11"><em>Es sind bisher keine Trainingsdaten vorhanden.</em></td></tr>';
			echo HTML::spaceTR(11);
			echo '</table>';
			return;
		}

		// Years
		$i = 0;
		echo '<tr class="a1 r"><td class="c b">Jahre</td>';
		foreach ($this->years as $i => $year) {
			$link = DataBrowser::getLink(Helper::Km($year['km']), mktime(0,0,0,1,1,$year['year']), mktime(23,59,50,12,31,$year['year']));
			echo '<td><span title="'.$year['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 9; $i++)
			echo HTML::emptyTD();
		echo '</tr>';

		// Months
		$i = 0;
		echo '<tr class="a1 r"><td class="c b">Monate</td>';
		foreach ($this->months as $i => $month) {
			$link = DataBrowser::getLink(Helper::Km($month['km']), mktime(0,0,0,$month['month'],1,$month['year']), mktime(23,59,50,$month['month']+1,0,$month['year']));
			echo '<td><span title="'.Helper::Month($month['month']).' '.$month['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 9; $i++)
			echo HTML::emptyTD();
		echo '</tr>';

		// Weeks
		$i = 0;
		echo '<tr class="a1 r"><td class="c b">Wochen</td>';
		foreach ($this->weeks as $i => $week) {
			$link = DataBrowser::getLink(Helper::Km($week['km']), Helper::Weekstart($week['time']), Helper::Weekend($week['time']));
			echo '<td><span title="KW '.$week['week'].' '.$week['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 9; $i++)
			echo HTML::emptyTD();
		echo '</tr>';

		echo HTML::spaceTR(11);
		echo '</table>';
	}

	/**
	 * Initialize $this->ABCData
	 */
	private function initData() {
		$this->rekorde = array();
		$this->rekorde[] = array('name' => 'Schnellsten Trainings',
			'sportquery' => 'SELECT * FROM `'.PREFIX.'sport` WHERE `distances`=1 ORDER BY `id` ASC',
			'datquery' => 'SELECT `id`, `time`, `s`, `distance`, `sportid` FROM `'.PREFIX.'training` WHERE `sportid`=\'.$sport[\'id\'].\' ORDER BY (`distance`/`s`) DESC, `s` DESC LIMIT 10',
			'eval' => '0');
		$this->rekorde[] = array('name' => 'L&auml;ngsten Trainings',
			'sportquery' => 'SELECT * FROM `'.PREFIX.'sport` ORDER BY `id` ASC',
			'datquery' => 'SELECT * FROM `'.PREFIX.'training` WHERE `sportid`=\'.$sport[\'id\'].\' ORDER BY `distance` DESC, `s` DESC LIMIT 10',
			'eval' => '1');

		$this->years = Mysql::getInstance()->fetchAsArray('
			SELECT
				`sportid`,
				SUM(`distance`) as `km`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.CONF_RUNNINGSPORT.'
			GROUP BY `year`
			ORDER BY `km` DESC
			LIMIT 10');
		
		$this->months = Mysql::getInstance()->fetchAsArray('
			SELECT
				`sportid`,
				SUM(`distance`) as `km`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`,
				(MONTH(FROM_UNIXTIME(`time`))+100*YEAR(FROM_UNIXTIME(`time`))) as `monthyear`
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.CONF_RUNNINGSPORT.'
			GROUP BY `monthyear`
			ORDER BY `km` DESC
			LIMIT 10');

		$this->weeks = Mysql::getInstance()->fetchAsArray('
			SELECT
				`sportid`,
				SUM(`distance`) as `km`,
				WEEK(FROM_UNIXTIME(`time`),1) as `week`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				YEARWEEK(FROM_UNIXTIME(`time`),1) as `weekyear`,
				`time`
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.CONF_RUNNINGSPORT.'
			GROUP BY `weekyear`
			ORDER BY `km` DESC
			LIMIT 10');
	}
}
?>