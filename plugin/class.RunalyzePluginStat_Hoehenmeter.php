<?php
/**
 * This file contains the class of the RunalyzePluginStat "Hoehenmeter".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Hoehenmeter';
/**
 * Class: RunalyzePluginStat_Hoehenmeter
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Hoehenmeter extends PluginStat {
	private $ElevationData = array();
	private $SumData       = array();
	private $UpwardData    = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'H&ouml;henmeter';
		$this->description = 'Die steilsten und bergigsten L&auml;ufe sowie der &Uuml;berblick &uuml;ber die absolvierten H&ouml;henmeter aller Monate.';
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
		$this->initElevationData();
		$this->initSumData();
		$this->initUpwardData();
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayHeader('H&ouml;henmeter');
		$this->displayElevationData();
		$this->displaySumData();
		$this->displayUpwardData();

		echo HTML::clearBreak();
	}

	/**
	 * Display the table with summed data for every month 
	 */
	private function displayElevationData() {
		echo '<table class="small fullWidth r">';
		echo HTML::monthTr(8, 1);
		echo HTML::spaceTR(13);

		if (empty($this->ElevationData))
			echo '<tr><td colspan="12"><em>Keine Strecken gefunden.</em></td></tr>';
		foreach ($this->ElevationData as $y => $Data) {
			echo('
				<tr class="a'.($y%2+1).'">
					<td class="b l">'.$y.'</td>'.NL);

			for ($m = 1; $m <= 12; $m++) {
				if (isset($Data[$m]) && $Data[$m]['elevation'] > 0) {
					$Link = new SearchLink();
					$Link->fromTo(mktime(0,0,0,$m,1,$y), mktime(0,0,0,$m+1,0,$y));
					$Link->sortBy('elevation');

					echo '<td>'.$Link->link($Data[$m]['elevation'].'&nbsp;hm').'</td>';
				} else {
					echo HTML::emptyTD();
				}
			}

			echo '</tr>'.NL;
		}

		echo HTML::spaceTR(13);
		echo '</table>';
	}

	/**
	 * Display the table for routes with highest elevation
	 */
	private function displaySumData() {
		echo '<table style="width:48%;" style="margin:0 5px;" class="left small">';
		echo '<tr class="b c"><td colspan="3">Meisten H&ouml;henmeter</td></tr>';
		echo HTML::spaceTR(4);

		if (empty($this->SumData))
			echo '<tr><td colspan="4"><em>Keine Strecken gefunden.</em></td></tr>';

		foreach ($this->SumData as $i => $Data) {
			$Training = new TrainingObject($Data);

			if (strlen($Data['route']) == 0)
				$Data['route'] = '<em>unbekannte Strecke</em>';

			echo('
			<tr class="a'.($i%2+1).'">
				<td class="small">'.$Training->DataView()->getDateAsWeeklink().'</td>
				<td>'.$Training->Linker()->linkWithSportIcon().'</td>
				<td title="'.($Data['comment'] != "" ? $Data['comment'].': ' : '').$Data['route'].'">'.$Data['route'].'</td>
				<td class="r">'.$Data['elevation'].'&nbsp;hm</td>
			</tr>
				'.NL);
		}

		echo HTML::spaceTR(4);
		echo '</table>';
	}

	/**
	 * Display the table for routes with procentual highest elevation
	 */
	private function displayUpwardData() {
		echo '<table style="width:48%;" style="margin:0 5px;" class="right small">';
		echo '<tr class="b c"><td colspan="3">Steilsten Strecken</td></tr>';
		echo HTML::spaceTR(4);

		if (empty($this->UpwardData))
			echo '<tr><td colspan="4"><em>Keine Strecken gefunden.</em></td></tr>';

		foreach ($this->UpwardData as $i => $Data) {
			$Training = new TrainingObject($Data);

			if (strlen($Data['route']) == 0)
				$Data['route'] = '<em>unbekannte Strecke</em>';

			echo('
			<tr class="a'.($i%2+1).'">
				<td class="small">'.$Training->DataView()->getDateAsWeeklink().'</td>
				<td>'.$Training->Linker()->linkWithSportIcon().'</td>
				<td title="'.($Data['comment'] != "" ? $Data['comment'].': ' : '').$Data['route'].'">'.$Data['route'].'</td>
				<td class="r">
					'.round($Data['steigung']/10, 2).'&nbsp;&#37;<br />
					<small>('.$Data['elevation'].'&nbsp;hm/'.$Data['distance'].'&nbsp;km</small>
				</td>
			</tr>
				'.NL);
		}

		echo HTML::spaceTR(4);
		echo '</table>';
	}

	/**
	 * Initialize $this->ElevationData
	 */
	private function initElevationData() {
		$result = Mysql::getInstance()->fetchAsArray('
			SELECT
				SUM(`elevation`) as `elevation`,
				SUM(`distance`) as `km`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`
			FROM `'.PREFIX.'training`
			WHERE `elevation` > 0
			GROUP BY `year`, `month`');

		foreach ($result as $dat) {
			$this->ElevationData[$dat['year']][$dat['month']] = array(
				'elevation' => $dat['elevation'],
				'km' => $dat['km'],
			);
		}
	}

	/**
	 * Initialize $this->SumData
	 */
	private function initSumData() {
		$this->SumData = Mysql::getInstance()->fetchAsArray('
			SELECT
				`time`, `sportid`, `id`, `elevation`, `route`, `comment`, `s`, `distance`
			FROM `'.PREFIX.'training`
			WHERE `elevation` > 0
			ORDER BY `elevation` DESC
			LIMIT 10');
	}

	/**
	 * Initialize $this->UpwardData
	 */
	private function initUpwardData() {
		$this->UpwardData = Mysql::getInstance()->fetchAsArray('
			SELECT
				`time`, `sportid`, `id`, `elevation`, `route`, `comment`,
				(`elevation`/`distance`) as `steigung`, `distance`, `s`
			FROM `'.PREFIX.'training`
			WHERE `elevation` > 0
			ORDER BY `steigung` DESC
			LIMIT 10');
	}
}