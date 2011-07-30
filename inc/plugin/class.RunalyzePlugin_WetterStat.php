<?php
/**
 * This file contains the class of the RunalyzePlugin "WetterStat".
 */
$PLUGINKEY = 'RunalyzePlugin_WetterStat';
/**
 * Class: RunalyzePlugin_WetterStat
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
class RunalyzePlugin_WetterStat extends PluginStat {
	private $i      = 0;
	private $jahr   = '';
	private $jstart = 0;
	private $jende  = 0;

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Wetter';
		$this->description = 'Wetterverh&auml;ltnisse, Temperaturen und die getragenen Kleidungsst&uuml;cke.';

		$this->initData();
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['for_weather']  = array('type' => 'bool', 'var' => true, 'description' => 'Wetter-Statistiken anzeigen');
		$config['for_clothes']  = array('type' => 'bool', 'var' => true, 'description' => 'Kleidung-Statistiken anzeigen');

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		if (!$this->config['for_weather']['var'] && !$this->config['for_clothes']['var']) {
			echo ('<em>In der Konfiguration sind sowohl die Wetter- als auch die Kleidungs-Statistiken ausgeschaltet. So macht das keinen Sinn ;)</em>');
			return;
		}

		if ($this->config['for_weather']['var'])
			echo Ajax::window('<a class="right" href="inc/plugin/window.wetter.php" title="Wetter-Diagramme anzeigen">'.Icon::get(Icon::$FATIGUE, 'Wetter-Diagramme anzeigen').'</a>');

		$this->displayHeader($this->getHeader());
		$this->displayYearNavigation();
		echo '<small class="left">'.Helper::Sport(CONF_MAINSPORT).'</small>';
		echo Helper::clearBreak();
		echo '<br />';
		
		$this->displayExtremeTrainings();
		$this->displayMonthTable();
		$this->displayClothesTable();
	}

	/**
	 * Display month-table
	 */
	private function displayMonthTable() {
		echo '<table style="width:100%;" class="small">';
		echo Helper::monthTR(8, 1);

		if ($this->config['for_weather']['var']) {
			echo Helper::spaceTR(13);
			$this->displayMonthTableTemp();
			$this->displayMonthTableWeather();
		}

		if ($this->config['for_clothes']['var']) {
			echo Helper::spaceTR(13);
			$this->displayMonthTableClothes();
		}

		echo Helper::spaceTR(13);
		echo '<tr>'.Helper::emptyTD(13).'</tr>';
		echo '</table>';
	}

	/**
	* Display month-table for temperature
	*/
	private function displayMonthTableTemp() {
		echo '<tr class="a2 r"><td class="c">&#176;C</td>';

		$i = 1;
		$temps = Mysql::getInstance()->fetchAsArray('SELECT
				AVG(`temperatur`) as `temp`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`="'.CONF_MAINSPORT.'" AND
				`temperatur` IS NOT NULL
				'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
			GROUP BY MONTH(FROM_UNIXTIME(`time`))
			ORDER BY `m` ASC
			LIMIT 12');

		if (!empty($temps)) {
			foreach ($temps as $temp) {
				for (; $i < $temp['m']; $i++)
					echo Helper::emptyTD();
				$i++;
		
				echo '<td>'.round($temp['temp']).' &deg;C</td>'.NL;
			}

			for (; $i <= 12; $i++)
				echo Helper::emptyTD();
		} else {
			echo Helper::emptyTD(12);
		}

		echo '</tr>';
	}

	/**
	* Display month-table for weather
	*/
	private function displayMonthTableWeather() {
		$wetter_all = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'wetter` WHERE `name`!="unbekannt" ORDER BY `order` ASC');
		foreach ($wetter_all as $w => $wetter) {
			echo '<tr class="a'.($w%2+1).' r"><td class="c">'.Helper::WeatherImage($wetter['id']).'</td>';
		
			$i = 1;
			$data = Mysql::getInstance()->fetchAsArray('SELECT
					SUM(1) as `num`,
					MONTH(FROM_UNIXTIME(`time`)) as `m`
				FROM `'.PREFIX.'training` WHERE
					`sportid`="'.CONF_MAINSPORT.'" AND
					`wetterid`='.$wetter['id'].'
					'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
				GROUP BY MONTH(FROM_UNIXTIME(`time`))
				ORDER BY `m` ASC
				LIMIT 12');

			if (!empty($data)) {
				foreach ($data as $dat) {
					for (; $i < $dat['m']; $i++)
						echo Helper::emptyTD();
					$i++;
			
					echo ($dat['num'] != 0)
						? ('<td>'.$dat['num'].'x</td>'.NL)
						: Helper::emptyTD();
				}
			
				for (; $i <= 12; $i++)
					echo Helper::emptyTD();
			} else {
				echo Helper::emptyTD(12);
			}
		}
	
		echo '</tr>';
	}

	/**
	* Display month-table for clothes
	*/
	private function displayMonthTableClothes() {
		$nums = Mysql::getInstance()->fetchAsArray('SELECT
				SUM(1) as `num`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`="'.CONF_MAINSPORT.'" AND
				`kleidung`!=""
				'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
			GROUP BY MONTH(FROM_UNIXTIME(`time`))
			ORDER BY `m` ASC
			LIMIT 12');
		
		if (!empty($nums)) {
			foreach ($nums as $dat)
				$num[$dat['m']] = $dat['num'];
		} else {
			Error::getInstance()->addWarning('Bisher keine Trainingsdaten eingetragen', __FILE__, 169);
		}

		$kleidungen = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'kleidung` ORDER BY `order` ASC');
		if (!empty($kleidungen)) {
			foreach ($kleidungen as $k => $kleidung) {
				echo '<tr class="a'.($k%2+1).' r"><td class="r">'.$kleidung['name'].'</td>';
			
				$i = 1;
				$data = Mysql::getInstance()->fetchAsArray('SELECT
						SUM(IF(FIND_IN_SET("'.$kleidung['id'].'", `kleidung`)!=0,1,0)) as `num`,
						MONTH(FROM_UNIXTIME(`time`)) as `m`
					FROM `'.PREFIX.'training` WHERE
						`sportid`="'.CONF_MAINSPORT.'"
						'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
					GROUP BY MONTH(FROM_UNIXTIME(`time`))
					HAVING `num`!=0
					ORDER BY `m` ASC
					LIMIT 12');

				if (!empty($data)) {
					foreach ($data as $dat) {
						for (; $i < $dat['m']; $i++)
							echo Helper::emptyTD();
						$i++;

						if ($dat['num'] != 0)
							echo('
								<td class="r"><span title="'.$dat['num'].'x">
										'.round($dat['num']*100/$num[$dat['m']]).' &#37;
								</span></td>'.NL);
						else
							echo Helper::emptyTD();
					}

					for (; $i <= 12; $i++)
						echo Helper::emptyTD();
				} else {
					echo('		<td colspan="12" />'.NL);
				}

				echo('</tr>');
			}
		} else {
			$Error->addWarning('Keine Kleidung eingetragen', __FILE__, __LINE__); 
		}
	}

	/**
	 * Display table for clothes
	 */
	private function displayClothesTable() {
		echo '<table style="width:100%;" class="small">
			<tr class="b c">
				<td />
				<td>Temperaturen</td>
				<td>&Oslash;</td>
				<td colspan="2" />
				<td>Temperaturen</td>
				<td>&Oslash;</td>
				<td colspan="2" />
				<td>Temperaturen</td>
				<td>&Oslash;</td>
			</tr>';
		echo Helper::spaceTR(11);
		echo '<tr class="a1 r">';

		$kleidungen = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'kleidung` ORDER BY `order` ASC');
		if (!empty($kleidungen)) {
			foreach ($kleidungen as $i => $kleidung) {
				if ($i%3 == 0)
					echo '</tr><tr class="a'.($i%2+1).' r">';
				else
					echo '<td>&nbsp;&nbsp;</td>';

				$dat = Mysql::getInstance()->fetch('SELECT
						AVG(`temperatur`) as `avg`,
						MAX(`temperatur`) as `max`,
						MIN(`temperatur`) as `min`
					FROM `'.PREFIX.'training` WHERE `sportid`="'.CONF_MAINSPORT.'" AND
					`temperatur` IS NOT NULL AND
					FIND_IN_SET('.$kleidung['id'].',`kleidung`) != 0
					'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : ''));

				echo '<td class="l">'.$kleidung['name'].'</td>';

				if ($dat['min'] != '') {
					echo '<td>'.($dat['min']).'&deg;C bis '.($dat['max']).'&deg;C</td>';
					echo '<td>'.round($dat['avg']).'&deg;C</td>';
				} else {
					echo '<td colspan="2" class="c"><em>-</em></td>';
				}
			}
		} else
			$Error->addWarning('Keine Kleidung eingetragen', __FILE__, __LINE__);

		for (; $i%3 != 1; $i++)
			echo Helper::emptyTD(3);

		echo '</tr>';
		echo Helper::spaceTR(11);
		echo '</table>';
	}

	/**
	 * Display extreme trainings
	 */
	private function displayExtremeTrainings() {
		$hot  = Mysql::getInstance()->fetchAsArray('SELECT `temperatur`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperatur` IS NOT NULL '.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').' ORDER BY `temperatur` DESC LIMIT 3');
		$cold = Mysql::getInstance()->fetchAsArray('SELECT `temperatur`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperatur` IS NOT NULL '.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').' ORDER BY `temperatur` ASC LIMIT 3');

		foreach ($hot as $i => $h)
			$hot[$i] = $h['temperatur'].'&nbsp;&#176;C am '.Ajax::trainingLink($h['id'], date('d.m.Y', $h['time']));
		foreach ($cold as $i => $c)
			$cold[$i] = $c['temperatur'].'&nbsp;&#176;C am '.Ajax::trainingLink($h['id'], date('d.m.Y', $c['time']));

		echo '<small>';
		echo '<strong>W&auml;rmsten L&auml;ufe:</strong> '.NL;
		echo implode(', '.NL, $hot).'<br />'.NL;
		echo '<strong>K&auml;ltesten L&auml;ufe:</strong> '.NL;
		echo implode(', '.NL, $cold).'<br />'.NL;
		echo '</small>';
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		if ($this->year == -1) {
			$this->i      = 0;
			$this->jahr   = "Gesamt";
			$this->jstart = mktime(0,0,0,1,1,START_YEAR);
			$this->jende  = time();
		} else {
			$this->i      = $this->year;
			$this->jahr   = $this->year;
			$this->jstart = mktime(0,0,0,1,1,$this->i);
			$this->jende  = mktime(23,59,59,1,0,$this->i+1);
		}
	}

	/**
	 * Get header depending on config
	 */
	private function getHeader() {
		if ($this->config['for_weather']['var'])
			$header = 'Wetter';
		if ($this->config['for_clothes']['var']) {
			if ($header != '')
				$header .= ' und Kleidung';
			else
				$header = 'Kleidung';
		}

		return $header.': '.$this->jahr;
	}
}
?>