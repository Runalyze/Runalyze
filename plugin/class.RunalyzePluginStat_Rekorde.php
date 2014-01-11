<?php
/**
 * This file contains the class of the RunalyzePluginStat "Rekorde".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Rekorde';
/**
 * Class: RunalyzePluginStat_Rekorde
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Rekorde extends PluginStat {
	private $rekorde = array();
	private $months = array();
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
		$this->displayHeader('Rekorde');
		$this->displayRekorde();
		$this->displayMostKilometer();
	}

	/**
	 * Display the table with general records
	 */
	private function displayRekorde() {
		foreach ($this->rekorde as $rekord) {
			echo '<table class="small fullwidth zebra-style">';
			echo '<thead><tr><th colspan="11" class="l">'.$rekord['name'].'</th></tr></thead>';

			$output = false;
			eval('$sports = Mysql::getInstance()->fetchAsArray(\''.$rekord['sportquery'].'\');');
			foreach ($sports as $i => $sport) {
				eval('$data = Mysql::getInstance()->fetchAsArray(\''.$rekord['datquery'].'\');');

				if (!empty($data)) {
					$output = true;
					echo '<tbody>';
					echo '<tr class="r">';
					echo '<td class="b l">'.Icon::getSportIcon($sport['id']).' '.$sport['name'].'</td>';
	
					$j = 0;
					foreach ($data as $j => $dat) {
						if ($rekord['eval'] == 0)
							$code = SportFactory::getSpeedWithAppendixAndTooltip($dat['distance'], $dat['s'], $sport['id']);
						elseif ($rekord['eval'] == 1)
							$code = ($dat['distance'] != 0 ? Running::Km($dat['distance']) : Time::toString($dat['s']));
	
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
				echo '<tr><td colspan="11"><em>Es sind bisher keine Trainingsdaten vorhanden.</em></td></tr>';

			echo '</tbody>';
			echo '</table>';
		}
	}

	/**
	 * Display the table with most kilometer for each year/month/week
	 */
	private function displayMostKilometer() {
		echo '<table class="small fullwidth zebra-style">';
		echo '<thead><tr><th colspan="11" class="l">Trainingsreichsten Laufphasen</th></tr></thead>';
		echo '<tbody>';

		if (empty($this->years)) {
			echo '<tr><td colspan="11"><em>Es sind bisher keine Trainingsdaten vorhanden.</em></td></tr>';
			echo HTML::spaceTR(11);
			echo '</tbody>';
			echo '</table>';
			return;
		}

		// Years
		echo '<tr class="r"><td class="c b">Jahre</td>';
		foreach ($this->years as $i => $year) {
			$link = DataBrowserLinker::link(Running::Km($year['km']), mktime(0,0,0,1,1,$year['year']), mktime(23,59,50,12,31,$year['year']));
			echo '<td><span title="'.$year['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 9; $i++)
			echo HTML::emptyTD();
		echo '</tr>';

		// Months
		echo '<tr class="r"><td class="c b">Monate</td>';
		foreach ($this->months as $i => $month) {
			$link = DataBrowserLinker::link(Running::Km($month['km']), mktime(0,0,0,$month['month'],1,$month['year']), mktime(23,59,50,$month['month']+1,0,$month['year']));
			echo '<td><span title="'.Time::Month($month['month']).' '.$month['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 9; $i++)
			echo HTML::emptyTD();
		echo '</tr>';

		// Weeks
		echo '<tr class="r"><td class="c b">Wochen</td>';
		foreach ($this->weeks as $i => $week) {
			$link = DataBrowserLinker::link(Running::Km($week['km']), Time::Weekstart($week['time']), Time::Weekend($week['time']));
			echo '<td><span title="KW '.$week['week'].' '.$week['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 9; $i++)
			echo HTML::emptyTD();
		echo '</tr>';

		echo '</tbody>';
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
			'datquery' => 'SELECT `id`, `time`, `s`, `distance`, `sportid` FROM `'.PREFIX.'training` WHERE `sportid`=\'.$sport[\'id\'].\' ORDER BY `distance` DESC, `s` DESC LIMIT 10',
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