<?php
/**
 * This file contains the class of the RunalyzePluginStat "Rekorde".
 * @package Runalyze\Plugins\Stats
 */

use Runalyze\Configuration;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Pace;
use Runalyze\Util\Time;
use Runalyze\Model\Sport;

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
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Records');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Faster, longer, better: Your records from your activities.');
	}

	/**
	 * Init data
	 */
	protected function prepareForDisplay() {
		$this->setYearsNavigation(true, true, true);

		$this->setHeaderWithSportAndYear();

		$this->initData();
	}

	/**
	 * Title for all years
	 * @return string
	 */
	protected function titleForAllYears() {
		return __('All years');
	}

	/**
	 * Default sport
	 * @return int
	 */
	protected function defaultSport() {
		return -1;
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayRekorde();
		$this->displayMostKilometer();
	}

	/**
	 * Display the table with general records
	 */
	private function displayRekorde() {
		foreach ($this->rekorde as $rekord) {
			echo '<table class="fullwidth zebra-style">';
			echo '<thead><tr><th colspan="11" class="l">'.$rekord['name'].'</th></tr></thead>';
			echo '<tbody>';

			$output = false;
			$sports = DB::getInstance()->query($rekord['sportquery'])->fetchAll();
			$Request = DB::getInstance()->prepare($rekord['datquery']);

			foreach ($sports as $sportData) {
				$Request->bindValue('sportid', $sportData['id']);
				$Request->execute();
				$data = $Request->fetchAll();

				if (!empty($data)) {
					$Sport = new Sport\Entity($sportData);
					$output = true;
					echo '<tr class="r">';
					echo '<td class="b l">'.$Sport->icon()->code().' '.$Sport->name().'</td>';

					$j = 0;
					foreach ($data as $j => $dat) {
						if ($rekord['speed']) {
							$Pace = new Pace($dat['s'], $dat['distance']);
							$Pace->setUnit($Sport->legacyPaceUnit());
							$code = $Pace->valueWithAppendix();
						} else {
							$code = ($dat['distance'] != 0 ? Distance::format($dat['distance']) : Duration::format($dat['s']));
						}

						echo '<td class="small"><span title="'.\Runalyze\Util\LocalTime::date("d.m.Y",$dat['time']).'">
								'.Ajax::trainingLink($dat['id'], $code).'
							</span></td>';
					}

					for (; $j < 9; $j++)
						echo HTML::emptyTD();

					echo '</tr>';
				}
			}

			if (!$output)
				echo '<tr><td colspan="11"><em>'.__('No data available').'</em></td></tr>';

			echo '</tbody>';
			echo '</table>';
		}
	}

	/**
	 * Display the table with most kilometer for each year/month/week
	 */
	private function displayMostKilometer() {
		echo '<table class="fullwidth zebra-style">';
		echo '<thead><tr><th colspan="11" class="l">'.__('Most kilometers').'</th></tr></thead>';
		echo '<tbody>';

		if (empty($this->weeks)) {
			echo '<tr><td colspan="11"><em>'.__('No data available').'</em></td></tr>';
			echo HTML::spaceTR(11);
			echo '</tbody>';
			echo '</table>';
			return;
		}

		// Years
		if ($this->year == -1) {
			echo '<tr class="r"><td class="c b">'.__('per year').'</td>';
			foreach ($this->years as $i => $year) {
				$link = DataBrowserLinker::yearLink(Distance::format($year['km']), $year['time'], false);
				echo '<td class="small"><span title="'.$year['year'].'">'.$link.'</span></td>';
			}
			for (; $i < 9; $i++)
				echo HTML::emptyTD();
			echo '</tr>';
		}

		// Months
		echo '<tr class="r"><td class="c b">'.__('per month').'</td>';
		foreach ($this->months as $i => $month) {
			$link = DataBrowserLinker::monthLink(Distance::format($month['km']), $month['time'], false);
			echo '<td class="small"><span title="'.Time::month($month['month']).' '.$month['year'].'">'.$link.'</span></td>';
		}
		for (; $i < 9; $i++)
			echo HTML::emptyTD();
		echo '</tr>';

		// Weeks
		echo '<tr class="r"><td class="c b">'.__('per week').'</td>';
		foreach ($this->weeks as $i => $week) {
			$link = DataBrowserLinker::weekLink(Distance::format($week['km']), $week['time'], false);
			echo '<td class="small"><span title="'.__('Week').' '.$week['week'].' '.$week['year'].'">'.$link.'</span></td>';
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
		// TODO: Use sport arrays from SportFactory
		$this->rekorde = array();
		$this->rekorde[] = array(
			'name'			=> __('Fastest activities'),
			'sportquery'	=> 'SELECT * FROM `'.PREFIX.'sport` WHERE `accountid`='.SessionAccountHandler::getId().' ORDER BY `id` ASC',
			'datquery'		=> 'SELECT `id`, `time`, `s`, `distance`, `sportid` FROM `'.PREFIX.'training` WHERE `accountid`='.SessionAccountHandler::getId().' AND `sportid`=:sportid '.$this->getYearDependenceForQuery().' AND `distance`>0 ORDER BY (`distance`/`s`) DESC, `s` DESC LIMIT 10',
			'speed'			=> true);
		$this->rekorde[] = array(
			'name'			=> __('Longest activities'),
			'sportquery'	=> 'SELECT * FROM `'.PREFIX.'sport` WHERE `accountid`='.SessionAccountHandler::getId().' ORDER BY `id` ASC',
			'datquery'		=> 'SELECT `id`, `time`, `s`, `distance`, `sportid` FROM `'.PREFIX.'training` WHERE `accountid`='.SessionAccountHandler::getId().' AND `sportid`=:sportid '.$this->getYearDependenceForQuery().' ORDER BY `distance` DESC, `s` DESC LIMIT 10',
			'speed'			=> false);

		if ($this->showsAllYears()) {
			$this->years = DB::getInstance()->query('
				SELECT
					`sportid`,
					SUM(`distance`) as `km`,
					YEAR(FROM_UNIXTIME(`time`)) as `year`,
					`time`
				FROM `'.PREFIX.'training`
				WHERE `accountid`='.SessionAccountHandler::getId().' AND
						`sportid`='.Configuration::General()->runningSport().'
				GROUP BY `year`
				ORDER BY `km` DESC
				LIMIT 10')->fetchAll();
		}

		$this->months = DB::getInstance()->query('
			SELECT
				`sportid`,
				SUM(`distance`) as `km`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`,
				(MONTH(FROM_UNIXTIME(`time`))+100*YEAR(FROM_UNIXTIME(`time`))) as `monthyear`,
				`time`
			FROM `'.PREFIX.'training`
			WHERE `accountid`='.\SessionAccountHandler::getId().' AND
					`sportid`='.Configuration::General()->runningSport().' '.$this->getYearDependenceForQuery().'
			GROUP BY `monthyear`
			ORDER BY `km` DESC
			LIMIT 10')->fetchAll();

		$this->weeks = DB::getInstance()->query('
			SELECT
				`sportid`,
				SUM(`distance`) as `km`,
				'.Configuration::General()->weekStart()->mysqlWeek('FROM_UNIXTIME(`time`)').' as `week`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				'.Configuration::General()->weekStart()->mysqlYearWeek('FROM_UNIXTIME(`time`)').' as `weekyear`,
				`time`
			FROM `'.PREFIX.'training`
			WHERE `accountid`='.\SessionAccountHandler::getId().' AND
					`sportid`='.Configuration::General()->runningSport().' '.$this->getYearDependenceForQuery().'
			GROUP BY `weekyear`
			ORDER BY `km` DESC
			LIMIT 10')->fetchAll();
	}
}
