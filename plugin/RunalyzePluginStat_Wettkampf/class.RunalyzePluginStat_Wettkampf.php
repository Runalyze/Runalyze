<?php
/**
 * This file contains the class of the RunalyzePluginStat "Wettkampf".
 */
$PLUGINKEY = 'RunalyzePluginStat_Wettkampf';
/**
 * Class: RunalyzePluginStat_Wettkampf
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Helper
 */
class RunalyzePluginStat_Wettkampf extends PluginStat {
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
		$config['fun_ids']        = array('type' => 'array', 'var' => array(), 'description' => 'IDs der Spa&szlig;-Wettk&auml;mpfe (nicht per Hand editieren!)');

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->handleGetData();

		$this->displayHeader($this->name);
		$this->displayNavigation();
		echo HTML::clearBreak();

		echo '<div id="wk-tablelist" class="change">'.NL;
			$this->displayWeatherStatistics();
			$this->displayAllCompetitions();
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
		echo Ajax::change('Wettk&auml;mpfe', 'tab_content', '#wk-tablelist').' |'.NL;
		echo Ajax::change('Bestzeiten', 'tab_content', '#bestzeiten').NL;
		echo '</small>';
	}

	/**
	 * Display all competitions
	 */
	private function displayAllCompetitions() {
		$this->displayTableStart();
		
		$wks = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID.' ORDER BY `time` DESC');
		$num = count($wks);
		if ($num > 0) {
			foreach($wks as $i => $wk) {
				$this->displayWkTr($wk, $i);
			}
		} else {
			$this->displayEmptyTr(1, 'Keine Wettk&auml;mpfe gefunden.');
			Error::getInstance()->addWarning('Keine Trainingsdaten vorhanden', __FILE__, __LINE__);
		}
		
		$this->displayTableEnd();

		if ($num >= $this->config['last_wk_num']['var'])
			echo '<small class="right link" onclick="$(\'#wk-table tr.allWKs\').toggleClass(\'hide\');$(\'table\').trigger(\'update\'); ">alle Wettk&auml;mpfe anzeigen</small>';

		echo HTML::clearBreak();
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
		$dists = Mysql::getInstance()->fetchAsArray('SELECT `distance`, SUM(1) as `wks` FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID.' GROUP BY `distance`');
		foreach ($dists as $i => $dist) {
			if ($dist['wks'] > 1 || in_array($dist['distance'], $this->config['pb_distances']['var'])) {
				$this->distances[] = $dist['distance'];
		
				$wk = Mysql::getInstance()->fetchSingle('SELECT * FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID.' AND `distance`='.$dist['distance'].' ORDER BY `s` ASC');
				$this->displayWKTr($wk, $i, true);
			}
		}

		if (empty($this->distances))
			$this->displayEmptyTr(1, '<em>Es konnten auf den eingetragenen Distanzen keine Bestzeiten gefunden werden.</em>');
	}

	/**
	 * Display all image-links for personal bests
	 */
	private function displayPersonalBestsImages() {
		echo '<small style="text-align:center;display:block;">';

		$first = true;
		foreach ($this->distances as $km) {
			$name = Helper::Km($km, (round($km) != $km ? 1 : 0), ($km <= 3));

			echo (!$first ? ' | ' : '');
			echo Ajax::flotChange($name, 'bestzeitenFlots', 'bestzeit'.($km*1000));
			$first = false;
		}
		
		$display_km = $this->distances[0];
		if (in_array($this->config['main_distance']['var'], $this->distances))
			$display_km = $this->config['main_distance']['var'];

		echo '</small>';
		echo '<div id="bestzeitenFlots" class="flotChangeable" style="position:relative;width:482px;height:192px;margin:5px auto;">';
		foreach ($this->distances as $km) {
			echo Plot::getInnerDivFor('bestzeit'.($km*1000), 480, 190, $km != $display_km);
			$_GET['km'] = $km;
			include 'Plot.Bestzeit.php';
		}
		echo '</div>';
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
		
		$wks = Mysql::getInstance()->fetchAsArray('SELECT YEAR(FROM_UNIXTIME(`time`)) as `y`, `distance`, `s` FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID.' ORDER BY `y` ASC');
		foreach ($wks as $wk) {
			if (!isset($year[$wk['y']])) {
				$year[$wk['y']] = $dists;
				$year[$wk['y']]['sum'] = 0;
				$year['sum'] = 0;
			}
			$year[$wk['y']]['sum']++;
			foreach($kms as $km)
				if ($km == $wk['distance']) {
					$year[$wk['y']][$km]['sum']++;
					if ($wk['s'] < $year[$wk['y']][$km]['pb'])
						$year[$wk['y']][$km]['pb'] = $wk['s'];
				}
		}

		echo '<table style="width:100%;">';
		echo '<thead>';
		echo '<tr>';
		echo '<th></th>';

		foreach ($year as $y => $y_dat)
			if ($y != 'sum')
				echo('
					<th>'.$y.'</th>');

		echo '</tr>';
		echo '</thead>';

		foreach ($kms as $i => $km) {
			echo '<tr class="a'.($i%2+1).' r"><td class="b">'.Helper::Km($km, 1, $km <= 3).'</td>';
		
			foreach ($year as $key => $y)
				if ($key != 'sum')
					echo '<td>'.($y[$km]['sum'] != 0 ? '<small>'.Helper::Time($y[$km]['pb']).'</small> '.$y[$km]['sum'].'x' : '<em><small>kein Ergebnis</small></em>').'</td>';
		
			echo '</tr>';
		}

		echo HTML::spaceTR(count($year));

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
			<table cellspacing="0" width="100%" class="sortable autosort" id="wk-table">
				<thead>
					<tr class="c">
						<th class="{sorter: false}">&nbsp;</th>
						<th class="{sorter: \'germandate\'}">Datum</th>
						<th>Lauf</th>
						<th class="{sorter: \'distance\'}">Distanz</th>
						<th class="{sorter: \'resulttime\'}">Zeit</th>
						<th>Pace</th>'.(CONF_USE_PULS ? '
						<th>Puls</th>' : '').''.(CONF_USE_WETTER ? '
						<th class="{sorter: \'temperature\'}">Wetter</th>' : '').'
					</tr>
				</thead>
				<tbody>');
	}

	/**
	 * Display table-row for a competition
	 * @param unknown_type $wk
	 * @param unknown_type $i
	 * @param bool $all Show all rows
	 */
	private function displayWKTr($wk, $i, $all = false) {
		$Training = new Training($wk['id']);
		$hide = (!$all && $i >= $this->config['last_wk_num']['var']) ? ' allWKs hide' : '';

		echo('
			<tr class="a'.($i%2 + 1).$hide.' r">
				<td>'.$this->getIconForCompetition($wk['id']).'</td>
				<td class="c small">'.$Training->getDateAsWeeklink().'</a></td>
				<td class="l"><strong>'.$Training->trainingLinkWithComment().'</strong></td>
				<td>'.$Training->getDistanceStringWithoutEmptyDecimals().'</td>
				<td>'.$Training->getTimeString().'</td>
				<td class="small">'.$Training->getSpeedString().'</td>'.(CONF_USE_PULS ? '
				<td class="small">'.Helper::Unknown($Training->get('pulse_avg')).' / '.Helper::Unknown($Training->get('pulse_max')).' bpm</td>' : '').''.(CONF_USE_WETTER ? '
				<td class="small">'.$Training->Weather()->asString().'</td>' : '').'
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
				<td colspan="8">'.$text.'</td>
			</tr>');
	}

	/**
	 * Display table end
	 */
	private function displayTableEnd() {
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display statistics for weather
	 */
	private function displayWeatherStatistics() {
		$Strings = array();
		$Weather = Mysql::getInstance()->fetchAsArray('SELECT SUM(1) as num, weatherid FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID.' AND `weatherid`!="'.Weather::$UNKNOWN_ID.'" GROUP BY `weatherid` ORDER BY `weatherid` ASC');
		foreach ($Weather as $W)
			$Strings[] = $W['num'].'x '.Icon::getWeatherIcon($W['weatherid']);

		echo '<strong>Wetterstatistiken:</strong> ';
		echo implode(', ', $Strings);
		echo '<br /><br />';
	}

	/**
	 * Get linked icon for this competition
	 * @param int $id ID of the training
	 * @return string
	 */
	private function getIconForCompetition($id) {
		if ($this->isFunCompetition($id)) {
			$tag = 'nofun';
			$icon = Icon::get(Icon::$COMPETITION_FUN, "Spa&szlig;-Wettkampf | Klicken, um als normalen Wettkampf zu markieren");
		} else {
			$tag = 'fun';
			$icon = Icon::get(Icon::$COMPETITION, "Wettkampf | Klicken, um als Spa&szlig;-Wettkampf zu markieren");
		}

		return $this->getInnerLink($icon, 0, 0, $tag.'-'.$id);
	}

	/**
	 * Handle data from get-variables
	 */
	private function handleGetData() {
		if (isset($_GET['dat']) && strlen($_GET['dat']) > 0) {
			$parts = explode('-', $_GET['dat']);
			$tag   = $parts[0];
			$id    = $parts[1];

			if ($tag == 'fun' && is_numeric($id)) {
				$this->config['fun_ids']['var'][] = $id;
			} elseif ($tag == 'nofun' && is_numeric($id)) {
				if (($index = array_search($id, $this->config['fun_ids']['var'])) !== FALSE)
					unset($this->config['fun_ids']['var'][$index]);
			}

			$this->updateConfigVarToDatabase();
		}
	}

	/**
	 * Is this competition just for fun?
	 * @param int $id
	 * @return bool
	 */
	public function isFunCompetition($id) {
		return (in_array($id, $this->config['fun_ids']['var']));
	}
}
?>