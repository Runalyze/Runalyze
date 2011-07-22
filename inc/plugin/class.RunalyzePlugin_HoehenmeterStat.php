<?php
/**
 * This file contains the class of the RunalyzePlugin "HoehenmeterStat".
 */
$PLUGINKEY = 'RunalyzePlugin_HoehenmeterStat';
/**
 * Class: RunalyzePlugin_HoehenmeterStat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Helper
 * @uses START_YEAR
 *
 * Last modified 2011/07/10 13:00 by Hannes Christiansen
 */
class RunalyzePlugin_HoehenmeterStat extends PluginStat {
	private $ElevationData = array();
	private $SumData       = array();
	private $UpwardData    = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'H&ouml;henhmeter';
		$this->description = 'Die steilsten und bergigsten L&auml;ufe sowie der &Uuml;berblick &uuml;ber die absolvierten H&ouml;henmeter aller Monate.';

		$this->initElevationData();
		$this->initSumData();
		$this->initUpwardData();
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
		$this->displayHeader('H&ouml;henmeter');
		$this->displayElevationData();
		$this->displaySumData();
		$this->displayUpwardData();

		echo Helper::clearBreak();
	}

	/**
	 * Display the table with summed data for every month 
	 */
	private function displayElevationData() {
		echo '<table style="width:100%;" class="small">';
		echo Helper::monthTr(8, 1);
		echo Helper::spaceTR(13);
		
		foreach ($this->ElevationData as $y => $Data) {
			echo('
				<tr class="a'.($y%2+1).' r">
					<td class="b l">'.$y.'</td>'.NL);

			for ($m = 1; $m <= 12; $m++) {
				if (isset($Data[$m]) && $Data[$m]['hm'] > 0)
					echo '<td>'.DataBrowser::getSearchLink($Data[$m]['hm'].' hm', 'sort=DESC&order=hm&time-gt=01.'.$m.'.'.$y.'&time-lt=00.'.($m+1).'.'.$y).'</td>'.NL;
				else
					echo Helper::emptyTD();
			}

			echo '</tr>'.NL;
		}

		echo Helper::spaceTR(13);
		echo '</table>';
	}

	/**
	 * Display the table for routes with highest elevation
	 */
	private function displaySumData() {
		echo '<table style="width:48%;" style="margin:0 5px;" class="left small">';
		echo '<tr class="b c"><td colspan="3">Meisten H&ouml;henmeter</td></tr>';
		echo Helper::spaceTR(4);

		if (empty($this->SumData))
			echo '<tr><td colspan="4"><em>Keine Strecken gefunden.</em></td></tr>';

		foreach ($this->SumData as $i => $Strecke) {
			$icon = Icon::getSportIcon($Strecke['sportid']);

			echo('
			<tr class="a'.($i%2+1).'">
				<td class="small">'.date("d.m.Y", $Strecke['time']).'</td>
				<td>'.Ajax::trainingLink($Strecke['id'], $icon).'</td>
				<td title="'.($Strecke['bemerkung'] != "" ? $Strecke['bemerkung'].': ' : '').$Strecke['strecke'].'">'.$Strecke['strecke'].'</td>
				<td class="r">'.$Strecke['hm'].'&nbsp;hm</td>
			</tr>
				'.NL);
		}

		echo Helper::spaceTR(4);
		echo '</table>';
	}

	/**
	 * Display the table for routes with procentual highest elevation
	 */
	private function displayUpwardData() {
		echo '<table style="width:48%;" style="margin:0 5px;" class="right small">';
		echo '<tr class="b c"><td colspan="3">Steilsten Strecken</td></tr>';
		echo Helper::spaceTR(4);

		if (empty($this->UpwardData))
			echo '<tr><td colspan="4"><em>Keine Strecken gefunden.</em></td></tr>';

		foreach ($this->UpwardData as $i => $Strecke) {
			$icon = Icon::getSportIcon($Strecke['sportid']);

			echo('
			<tr class="a'.($i%2+1).'">
				<td class="small">'.date("d.m.Y", $Strecke['time']).'</td>
				<td>'.Ajax::trainingLink($Strecke['id'], $icon).'</td>
				<td title="'.($Strecke['bemerkung'] != "" ? $Strecke['bemerkung'].': ' : '').$Strecke['strecke'].'">'.$Strecke['strecke'].'</td>
				<td class="r">
					'.round($Strecke['steigung']/10, 2).'&nbsp;&#37;<br />
					<small>('.$Strecke['hm'].'&nbsp;hm/'.$Strecke['distanz'].'&nbsp;km</small>
				</td>
			</tr>
				'.NL);
		}

		echo Helper::spaceTR(4);
		echo '</table>';
	}

	/**
	 * Initialize $this->ElevationData
	 */
	private function initElevationData() {
		$result = Mysql::getInstance()->fetchAsArray('
			SELECT
				SUM(`hm`) as `hm`,
				SUM(`distanz`) as `km`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`
			FROM `'.PREFIX.'training`
			WHERE `hm` > 0
			GROUP BY `year`, `month`');

		foreach ($result as $dat) {
			$this->ElevationData[$dat['year']][$dat['month']] = array(
				'hm' => $dat['hm'],
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
				`time`, `sportid`, `id`, `hm`, `strecke`, `bemerkung`
			FROM `'.PREFIX.'training`
			WHERE `hm` > 0
			ORDER BY `hm` DESC
			LIMIT 10');
	}

	/**
	 * Initialize $this->UpwardData
	 */
	private function initUpwardData() {
		$this->UpwardData = Mysql::getInstance()->fetchAsArray('
			SELECT
				`time`, `sportid`, `id`, `hm`, `strecke`, `bemerkung`,
				(`hm`/`distanz`) as `steigung`, `distanz`
			FROM `'.PREFIX.'training`
			WHERE `hm` > 0
			ORDER BY `steigung` DESC
			LIMIT 10');
	}
}
?>