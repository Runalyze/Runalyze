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
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Elevation');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Your steepest activities and an overview of your cumulated elevation.');
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		//$config['all_sports'] = array('type' => 'bool', 'var' => false, 'description' => 'Alle Sportarten ber&uuml;cksichtigen');

		return $config;
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->setSportsNavigation(true, true);
		$this->setYearsNavigation(true, true);

		$this->setHeaderWithSportAndYear();

		$this->initElevationData();
		$this->initSumData();
		$this->initUpwardData();
	}

	/**
	 * Default year
	 * @return int
	 */
	protected function defaultYear() {
		return -1;
	}

	/**
	 * Title for all years
	 * @return string
	 */
	protected function titleForAllYears() {
		return __('All years');
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		if ($this->year == -1)
			$this->displayElevationData();

		$this->displaySumData();
		$this->displayUpwardData();

		echo HTML::clearBreak();
	}

	/**
	 * Display the table with summed data for every month 
	 */
	private function displayElevationData() {
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>'.HTML::monthTr(8, 1).'</thead>';
		echo '<tbody>';

		if (empty($this->ElevationData))
			echo '<tr><td colspan="13" class="l"><em>'.__('No routes found.').'</em></td></tr>';
		foreach ($this->ElevationData as $y => $Data) {
			echo('
				<tr>
					<td class="b l">'.$y.'</td>'.NL);

			for ($m = 1; $m <= 12; $m++) {
				if (isset($Data[$m]) && $Data[$m]['elevation'] > 0) {
					$Link = new SearchLink();
					$Link->fromTo(mktime(0,0,0,$m,1,$y), mktime(0,0,0,$m+1,0,$y));
					$Link->sortBy('elevation');

					echo '<td>'.$Link->link($Data[$m]['elevation'].'&nbsp;m').'</td>';
				} else {
					echo HTML::emptyTD();
				}
			}

			echo '</tr>'.NL;
		}

		echo '</tbody></table>';
	}

	/**
	 * Display the table for routes with highest elevation
	 */
	private function displaySumData() {
		echo '<table style="width:48%;" style="margin:0 5px;" class="left zebra-style">';
		echo '<thead><tr class="b c"><th colspan="4">'.__('Most elevation').'</th></tr></thead>';
		echo '<tbody>';

		if (empty($this->SumData))
			echo '<tr><td colspan="4"><em>'.__('No routes found.').'</em></td></tr>';

		foreach ($this->SumData as $Data) {
			$Training = new TrainingObject($Data);

			if (strlen($Data['route']) == 0)
				$Data['route'] = '<em>'.__('unknown route').'</em>';

			echo('
			<tr>
				<td class="small">'.$Training->DataView()->getDateAsWeeklink().'</td>
				<td>'.$Training->Linker()->linkWithSportIcon().'</td>
				<td title="'.($Data['comment'] != "" ? $Data['comment'].': ' : '').$Data['route'].'">'.$Data['route'].'</td>
				<td class="r">'.$Data['elevation'].'&nbsp;m</td>
			</tr>
				'.NL);
		}

		echo '</tbody></table>';
	}

	/**
	 * Display the table for routes with procentual highest elevation
	 */
	private function displayUpwardData() {
		echo '<table style="width:48%;" style="margin:0 5px;" class="right zebra-style">';
		echo '<thead><tr class="b c"><th colspan="4">'.__('Steepest routes').'</th></tr></thead>';
		echo '<tbody>';

		if (empty($this->UpwardData))
			echo '<tr><td colspan="4"><em>'.__('No routes found.').'</em></td></tr>';

		foreach ($this->UpwardData as $Data) {
			$Training = new TrainingObject($Data);

			if (strlen($Data['route']) == 0)
				$Data['route'] = '<em>'.__('unknown route').'</em>';

			echo('
			<tr>
				<td class="small">'.$Training->DataView()->getDateAsWeeklink().'</td>
				<td>'.$Training->Linker()->linkWithSportIcon().'</td>
				<td title="'.($Data['comment'] != "" ? $Data['comment'].': ' : '').$Data['route'].'">'.$Data['route'].'</td>
				<td class="r">
					'.round($Data['steigung']/10, 2).'&nbsp;&#37;<br>
					<small>('.$Data['elevation'].'&nbsp;m/'.$Data['distance'].'&nbsp;km</small>
				</td>
			</tr>
				'.NL);
		}

		echo '</tbody></table>';
	}

	/**
	 * Initialize $this->ElevationData
	 */
	private function initElevationData() {
		$result = DB::getInstance()->query('
			SELECT
				SUM(`elevation`) as `elevation`,
				SUM(`distance`) as `km`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`
			FROM `'.PREFIX.'training`
			WHERE `elevation` > 0 '.$this->getSportAndYearDependenceForQuery().'
			GROUP BY `year`, `month`')->fetchAll();

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
		$this->SumData = DB::getInstance()->query('
			SELECT
				`time`, `sportid`, `id`, `elevation`, `route`, `comment`, `s`, `distance`
			FROM `'.PREFIX.'training`
			WHERE `elevation` > 0 '.$this->getSportAndYearDependenceForQuery().'
			ORDER BY `elevation` DESC
			LIMIT 10')->fetchAll();
	}

	/**
	 * Initialize $this->UpwardData
	 */
	private function initUpwardData() {
		$this->UpwardData = DB::getInstance()->query('
			SELECT
				`time`, `sportid`, `id`, `elevation`, `route`, `comment`,
				(`elevation`/`distance`) as `steigung`, `distance`, `s`
			FROM `'.PREFIX.'training`
			WHERE `elevation` > 0 '.$this->getSportAndYearDependenceForQuery().'
			ORDER BY `steigung` DESC
			LIMIT 10')->fetchAll();
	}

	/**
	 * Get where query for sport
	 * @return string
	 */
	/*private function andSportWhere() {
		if (!$this->config['all_sports']['var'])
			return 'AND `sportid`="'.CONF_RUNNINGSPORT.'"';

		return '';
	}*/
}