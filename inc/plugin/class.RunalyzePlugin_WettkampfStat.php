<?php
/**
 * This file contains the class of the RunalyzePlugin "WettkampfStat".
 */
$PLUGINKEY = 'RunalyzePlugin_WettkampfStat';
/**
 * Class: RunalyzePlugin_WettkampfStat
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
class RunalyzePlugin_WettkampfStat extends PluginStat {
	private $distances = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Wettk&auml;mpfe';
		$this->description = 'Bestzeiten und alles weitere zu den bisher gelaufenen Wettk&auml;mpfen.';
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['last_wk_num']    = array('type' => 'int', 'var' => 10, 'description' => 'Anzahl f&uuml;r letzte Wettk&auml;mpfe');
		$config['main_distance']  = array('type' => 'int', 'var' => 10, 'description' => 'Hauptdistanz (wird als Diagramm dargestellt)');
		$config['pb_distances']   = array('type' => 'array', 'var' => array(1, 3, 5, 10, 21.1, 42.2), 'description' => 'Distanzen f&uuml;r Bestzeit-Vergleich (kommagetrennt)');

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayHeader($this->name);
		$this->displayNavigation();
		echo Helper::clearBreak();

		echo '<div id="alle" class="change" style="display:none;">'.NL;
			$this->displayAllCompetitions();
		echo '</div>'.NL;
		echo '<div id="last_wks" class="change" style="display:block;">'.NL;
			$this->displayLastCompetitions();
		echo '</div>'.NL;
		echo '<div id="bestzeiten" class="change" style="display:none;">'.NL;
			$this->displayPersonalBests();
		echo '</div>'.NL;
	}

	/**
	 * Display navigation for all container
	 */
	private function displayNavigation() {
		echo '<small class="right">';
		echo Ajax::change('Alle Wettk&auml;mpfe', 'tab_content', '#alle').' |'.NL;
		echo Ajax::change('Letzten Wettk&auml;mpfe', 'tab_content', '#last_wks').' |'.NL;
		echo Ajax::change('Bestzeiten', 'tab_content', '#bestzeiten').NL;
		echo '</small>';
	}

	/**
	 * Display all competitions
	 */
	private function displayAllCompetitions() {
		$this->displayTableStart();
		
		$wks = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'training` WHERE `typid`='.WK_TYPID.' ORDER BY `time` DESC');
		foreach ($wks as $i => $wk)
			$this->displayWKTr($wk, $i);
		
		$this->displayTableEnd();
	}

	/**
	 * Display last competitions
	 */
	private function displayLastCompetitions() {
		$this->displayTableStart();
		
		$wks = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'training` WHERE `typid`='.WK_TYPID.' ORDER BY `time` DESC LIMIT '.$this->config['last_wk_num']['var']);
		if (count($wks) > 0) {
			foreach($wks as $i => $wk)
				$this->displayWkTr($wk, $i);
		} else {
			$this->displayEmptyTr(1, 'Keine Wettk&auml;mpfe gefunden.');
			Error::getInstance()->addWarning('Keine Trainingsdaten vorhanden', __FILE__, __LINE__);
		}
		
		$this->displayTableEnd();
	}

	/**
	 * Display all personal bests
	 */
	private function displayPersonalBests() {
		$this->displayTableStart();
		$this->displayPersonalBestsTRs();
		$this->displayTableEnd();

		if (!empty($this->distances))
			$this->displayPersonalBestsImages();

		$this->displayPersonalBestYears();
	}

	/**
	 * Display all table-rows for personal bests
	 */
	private function displayPersonalBestsTRs() {
		$this->distances = array();
		$dists = Mysql::getInstance()->fetchAsArray('SELECT `distanz`, SUM(1) as `wks` FROM `'.PREFIX.'training` WHERE `typid`='.WK_TYPID.' GROUP BY `distanz`');
		foreach ($dists as $i => $dist) {
			if ($dist['wks'] > 1) {
				$this->distances[] = $dist['distanz'];
		
				$wk = Mysql::getInstance()->fetchSingle('SELECT * FROM `'.PREFIX.'training` WHERE `typid`='.WK_TYPID.' AND `distanz`='.$dist['distanz'].' ORDER BY `dauer` ASC');
				$this->displayWKTr($wk, $i);
			}
		}
	}

	/**
	 * Display all image-links for personal bests
	 */
	private function displayPersonalBestsImages() {
		echo '<small style="text-align:center;display:block;">';

		$first = true;
		foreach ($this->distances as $km) {
			$name = Helper::Km($km, (round($km) != $km ? 1 : 0), ($km <= 3));

			echo (!$first ? '| ' : '');
			echo Ajax::imgChange('<a href="inc/draw/plugin.wettkampf.php?km='.$km.'">'.$name.'</a>','bestzeit-diagramm');
			$first = false;
		}
		
		$display_km = $this->distances[0];
		if (in_array($this->config['main_distance']['var'], $this->distances))
			$display_km = $this->config['main_distance']['var'];

		echo '</small>';
		echo '<div class="bigImg" style="height:190px;width:480px; margin:0 auto;">
				<img id="bestzeit-diagramm" src="inc/draw/plugin.wettkampf.php?km='.$display_km.'" width="480" height="190" />
			</div>';
	}

	/**
	 * Display comparison for all years for personal bests
	 */
	private function displayPersonalBestYears() {
		$year = array();
		$dists = array();
		$kms = (is_array($this->config['pb_distances']['var'])) ? $this->config['pb_distances']['var'] : array(3, 5, 10, 21.1, 42.2);
		foreach ($kms as $km)
			$dists[$km] = array('sum' => 0, 'pb' => INFINITY);
		
		$wks = Mysql::getInstance()->fetchAsArray('SELECT YEAR(FROM_UNIXTIME(`time`)) as `y`, `distanz`, `dauer` FROM `'.PREFIX.'training` WHERE `typid`='.WK_TYPID.' ORDER BY `y` ASC');
		foreach ($wks as $wk) {
			if (!isset($year[$wk['y']])) {
				$year[$wk['y']] = $dists;
				$year['sum'] = 0;
			}
			$year[$wk['y']]['sum']++;
			foreach($kms as $km)
				if ($km == $wk['distanz']) {
					$year[$wk['y']][$km]['sum']++;
					if ($wk['dauer'] < $year[$wk['y']][$km]['pb'])
						$year[$wk['y']][$km]['pb'] = $wk['dauer'];
				}
		}

		echo '<table style="width:100%;">';
		echo '<tr class="b c">';
		echo '<td></td>';

		foreach ($year as $y => $y_dat)
			if ($y != 'sum')
				echo('
					<td>'.$y.'</td>');

		echo '</tr>';
		echo Helper::spaceTR(count($year));

		foreach ($kms as $i => $km) {
			echo '<tr class="a'.($i%2+1).' r"><td class="b">'.Helper::Km($km, 1, $km <= 3).'</td>';
		
			foreach ($year as $key => $y)
				if ($key != 'sum')
					echo '<td>'.($y[$km]['sum'] != 0 ? '<small>'.Helper::Time($y[$km]['pb']).'</small> '.$y[$km]['sum'].'x' : '&nbsp;').'</td>';
		
			echo '</tr>';
		}

		echo Helper::spaceTR(count($year));

		echo '<tr class="a'.(($i+1)%2+1).' r">';
		echo '<td class="b">Gesamt</td>';

		foreach ($year as $i => $y)
			if ($i != 'sum')
				echo('
					<td>'.$y['sum'].'x</td>');

		echo '</tr>';
		echo '</table>';
	}

	/**
	 * Display table start
	 */
	private function displayTableStart() {
		echo('
			<table cellspacing="0" width="100%">
				<tr class="b c">
					<td>Datum</td>
					<td>Lauf</td>
					<td>Distanz</td>
					<td>Zeit</td>
					<td>Pace</td>'.(CONFIG_USE_PULS ? '
					<td>Puls</td>' : '').''.(CONFIG_USE_WETTER ? '
					<td>Wetter</td>' : '').'
				</tr>');
		echo Helper::spaceTR(7);
	}

	/**
	 * Display table-row for a competition
	 * @param unknown_type $wk
	 * @param unknown_type $i
	 */
	private function displayWKTr($wk, $i) {
		echo('
			<tr class="a'.($i%2 + 1).' r">
				<td class="c small">'.DataBrowser::getLink(date("d.m.Y", $wk['time']), Helper::Weekstart($wk['time']), Helper::Weekend($wk['time'])).'</a></td>
				<td class="l"><strong>'.Ajax::trainingLink($wk['id'], $wk['bemerkung']).'</strong></td>
				<td>'.Helper::Km($wk['distanz'], (round($wk['distanz']) != $wk['distanz'] ? 1 : 0), $wk['bahn']).'</td>
				<td>'.Helper::Time($wk['dauer']).'</td>
				<td class="small">'.$wk['pace'].'/km</td>'.(CONFIG_USE_PULS ? '
				<td class="small">'.Helper::Unknown($wk['puls']).' / '.Helper::Unknown($wk['puls_max']).' bpm</td>' : '').''.(CONFIG_USE_WETTER ? '
				<td class="small">'.($wk['temperatur'] != 0 && $wk['wetterid'] != 0 ? $wk['temperatur'].' &deg;C '.Helper::WeatherImage($wk['wetterid']) : '').'</td>' : '').'
			</tr>');	
	}

	/**
	 * Display an empty table-row
	 * @param int $i
	 * @param string $text [optional]
	 */
	private function displayEmptyTr($i, $text = '') {
		echo('
			<tr class="a'.($i%2 + 1).'">
				<td colspan="7">'.$text.'</td>
			</tr>');
	}

	/**
	 * Display table end
	 */
	private function displayTableEnd() {
		echo Helper::spaceTR(7);
		echo '</table>';
	}
}
?>