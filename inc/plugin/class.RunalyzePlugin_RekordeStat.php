<?php
/**
 * This file contains the class of the RunalyzePlugin "RekordeStat".
 */
$PLUGINKEY = 'RunalyzePlugin_RekordeStat';
/**
 * Class: RunalyzePlugin_RekordeStat
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
class RunalyzePlugin_RekordeStat extends PluginStat {
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
			echo Helper::spaceTR(11);

			eval('$sports = Mysql::getInstance()->fetchAsArray(\''.$rekord['sportquery'].'\');');
			foreach ($sports as $i => $sport) {
				echo '<tr class="a'.($i%2 + 1).' r">';
				echo '<td class="b l">'.Icon::getSportIcon($sport['id']).' '.$sport['name'].'</td>';

				eval('$data = Mysql::getInstance()->fetchAsArray(\''.$rekord['datquery'].'\');');
				if (empty($data))
					Error::getInstance()->addWarning('Keine Trainingsdaten vorhanden', __FILE__, __LINE__);

				foreach ($data as $j => $dat) {
					if ($rekord['eval'] == 0)
						$code = Helper::Speed($dat['distanz'], $dat['dauer'], $sport['id']);
					elseif ($rekord['eval'] == 1)
						$code = ($dat['distanz'] != 0 ? Helper::Km($dat['distanz']) : Helper::Time($dat['dauer']));

					echo('<td><span title="'.date("d.m.Y",$dat['time']).'">
							'.Ajax::trainingLink($dat['id'], $code).'
						</span></td>');
				}

				for (; $j < 10; $j++)
					echo Helper::emptyTD();

				echo '</tr>';
			}
	
			echo Helper::spaceTR(11);
			echo '</table>';
		}
	}

	/**
	 * Display the table with most kilometer for each year/month/week
	 */
	private function displayMostKilometer() {
		echo '<table style="width:100%;" class="small">';
		echo '<tr class="b"><td colspan="11">Trainingsreichsten Laufphasen</td></tr>';
		echo Helper::spaceTR(11);

		// Years
		echo '<tr class="a1 r"><td class="c b">Jahre</td>';
		foreach ($this->years as $i => $year) {
			$link = DataBrowser::getLink(Helper::Km($year['km']), mktime(0,0,0,1,1,$year['year']), mktime(23,59,50,12,31,$year['year']));
			echo '<td><span title="'.$year['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 10; $i++)
			echo Helper::emptyTD();
		echo '</tr>';

		// Months
		echo '<tr class="a1 r"><td class="c b">Monate</td>';
		foreach ($this->months as $i => $month) {
			$link = DataBrowser::getLink(Helper::Km($month['km']), mktime(0,0,0,$month['month'],1,$month['year']), mktime(23,59,50,$month['month']+1,0,$month['year']));
			echo '<td><span title="'.Helper::Month($month['month']).' '.$month['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 10; $i++)
			echo Helper::emptyTD();
		echo '</tr>';

		// Weeks
		echo '<tr class="a1 r"><td class="c b">Wochen</td>';
		foreach ($this->weeks as $i => $week) {
			$link = DataBrowser::getLink(Helper::Km($week['km']), Helper::Weekstart($week['time']), Helper::Weekend($week['time']));
			echo '<td><span title="KW '.$week['week'].' '.$week['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 10; $i++)
			echo Helper::emptyTD();
		echo '</tr>';

		echo '</table>';
	}

	/**
	 * Initialize $this->ABCData
	 */
	private function initData() {
		$this->rekorde = array();
		$this->rekorde[] = array('name' => 'Schnellsten Trainings',
			'sportquery' => 'SELECT * FROM `ltb_sports` WHERE `distanztyp`=1 ORDER BY `id` ASC',
			'datquery' => 'SELECT `id`, `time`, `dauer`, `distanz`, `sportid` FROM `ltb_training` WHERE `sportid`=\'.$sport[\'id\'].\' ORDER BY `pace` ASC, `dauer` DESC LIMIT 10',
			'eval' => '0');
		$this->rekorde[] = array('name' => 'L&auml;ngsten Trainings',
			'sportquery' => 'SELECT * FROM `ltb_sports` ORDER BY `id` ASC',
			'datquery' => 'SELECT * FROM `ltb_training` WHERE `sportid`=\'.$sport[\'id\'].\' ORDER BY `distanz` DESC, `dauer` DESC LIMIT 10',
			'eval' => '1');

		$this->years = Mysql::getInstance()->fetchAsArray('
			SELECT
				`sportid`,
				SUM(`distanz`) as `km`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`
			FROM `ltb_training`
			WHERE `sportid`='.RUNNINGSPORT.'
			GROUP BY `year`
			ORDER BY `km` DESC
			LIMIT 10');
		
		$this->months = Mysql::getInstance()->fetchAsArray('
			SELECT
				`sportid`,
				SUM(`distanz`) as `km`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`,
				(MONTH(FROM_UNIXTIME(`time`))+100*YEAR(FROM_UNIXTIME(`time`))) as `monthyear`
			FROM `ltb_training`
			WHERE `sportid`='.RUNNINGSPORT.'
			GROUP BY `monthyear`
			ORDER BY `km` DESC
			LIMIT 10');

		$this->weeks = Mysql::getInstance()->fetchAsArray('
			SELECT
				`sportid`,
				SUM(`distanz`) as `km`,
				WEEK(FROM_UNIXTIME(`time`),1) as `week`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				YEARWEEK(FROM_UNIXTIME(`time`),1) as `weekyear`,
				`time`
			FROM `ltb_training`
			WHERE `sportid`='.RUNNINGSPORT.'
			GROUP BY `weekyear`
			ORDER BY `km` DESC
			LIMIT 10');
	}
}
?>