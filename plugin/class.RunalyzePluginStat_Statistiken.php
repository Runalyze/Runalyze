<?php
/**
 * This file contains the class of the RunalyzePluginStat "Statistiken".
 */
$PLUGINKEY = 'RunalyzePluginStat_Statistiken';
/**
 * Class: RunalyzePluginStat_Statistiken
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class RunalyzePluginStat_Statistiken extends PluginStat {
	private $sport     = array();
	private $colspan   = 0;
	private $num       = 0;
	private $num_start = 0;
	private $num_end   = 0;
	private $line      = 0;

	private $CompleteData = array();
	private $StundenData  = array();
	private $KMData       = array();
	private $KMDataWeek   = array(); // = KMData / 52
	private $KMDataMonth  = array(); // = KMData / 12
	private $TempoData    = array();
	private $VDOTData     = array();
	private $TRIMPData    = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Statistiken';
		$this->description = 'Allgemeine Statistiken: Monatszusammenfassung in der Jahres&uuml;bersicht f&uuml;r alle Sportarten.';
	}

	protected function setOwnNavigation() {
		$SubLinks = array();

		for ($x = date("Y"); $x >= START_YEAR; $x--)
			$SubLinks[] = $this->getInnerLink($x, $this->sportid, $x, 'allWeeks');

		$Links   = array();
		$Links[] = array('tag' => '<a href="#">Alle Trainingswochen</a>', 'subs' => $SubLinks);

		$this->setToolbarNavigationLinks($Links);
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->initData();
		$this->initLineData();

		$this->setSportsNavigation();
		$this->setYearsNavigation();
		$this->setOwnNavigation();
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('Diese Statistiken bieten eine Auswertung f&uuml;r jeden Monat oder jedes Jahr.
					Dabei werden die wichtigen Gr&ouml;&szlig;en Dauer, Distanz, Tempo, VDOT und Trimp verglichen.');
		echo HTML::p('Au&szlig;erdem wird die Zusammenfassung der letzten 10 Trainingswochen so angezeigt,
					wie sie im Daten-Fenster f&uuml;r die aktuelle Woche zu finden ist.');
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['compare_weeks']    = array('type' => 'bool', 'var' => true, 'description' => 'Wochenkilometer vergleichen');
		$config['show_streak']      = array('type' => 'bool', 'var' => true, 'description' => 'Streak anzeigen');
		$config['show_streak_days'] = array('type' => 'int', 'var' => 10, 'description' => 'Mindestzahl f&uuml;r Streak zum Anzeigen (0 f&uuml;r immer)');

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayHeader($this->sport['name'].': '.$this->getYearString());

		if ($this->wantToShowAllWeeks()) {
			$this->displayWeekTable(true);
		} else {
			$this->displayYearTable();

			if ($this->config['show_streak']['var'])
				$this->displayStreak();

			$this->displayWeekTable();
		}
	}

	/**
	 * Boolean flag: Show all weeks?
	 * @return bool
	 */
	private function wantToShowAllWeeks() {
		return $this->dat == 'allWeeks';
	}

	/**
	 * Display table with data for each month 
	 */
	private function displayYearTable() {
		echo '<table class="small r fullWidth">';

		echo ($this->year == -1) ? HTML::yearTR(0, 1) : HTML::monthTR(8, 1);
		echo HTML::spaceTR($this->colspan);

		$this->displayLine('Stunden', $this->StundenData);

		if ($this->sport['distances'] != 0) {
			$this->displayLine('KM', $this->KMData);
			if ($this->year == -1) {
				$this->displayLine('&oslash;&nbsp;Wochen-KM', $this->KMDataWeek);
				$this->displayLine('&oslash;&nbsp;Monats-KM', $this->KMDataMonth);
			}
			$this->displayLine('&oslash;&nbsp;Tempo', $this->TempoData);
		}

		if ($this->sportid == CONF_RUNNINGSPORT && CONF_RECHENSPIELE)
			$this->displayLine('VDOT', $this->VDOTData);

		if (CONF_RECHENSPIELE)
			$this->displayLine('TRIMP', $this->TRIMPData);

		echo '</table>';
	}

	/**
	 * Display one statistic line
	 * @param string $title
	 * @param array $data Array containing all $data[] = array('i' => i, 'text' => '...')
	 */
	private function displayLine($title, $data) {
		$this->line++;

		echo '<tr class="a'.($this->line%2+1).'">';
		echo '<td class="b">'.$title.'</td>';

		if (empty($data)) {
			echo HTML::emptyTD($this->colspan);
		} else {
			$td_i = 0;
			foreach ($data as $i => $dat) {
				for (; ($this->num_start + $td_i) < $dat['i']; $td_i++)
					echo HTML::emptyTD();
				$td_i++;

				echo '<td>'.$dat['text'].'</td>'.NL;
			}

			for (; $td_i < $this->num; $td_i++)
				echo HTML::emptyTD();
		}

		echo '</tr>';
	}

	/**
	 * Display table with last week-statistics 
	 * @param bool $showAllWeeks
	 */
	private function displayWeekTable($showAllWeeks = false) {
		if ($this->year != date("Y") && !$showAllWeeks)
			return;

		$Dataset = new Dataset();

		if ($this->config['compare_weeks']['var'])
			$Dataset->activateKilometerComparison();

		echo '<table class="small notSmaller r fullWidth">';
		echo '<thead><tr><th colspan="'.($Dataset->column_count+1).'">'.($showAllWeeks?'Alle':'Letzten 10').' Trainingswochen</th></tr></thead>';
		echo '<tbody>';

		if (!$showAllWeeks) {
			$starttime = time();
			$maxW      = 9;
		} else {
			$starttime = ($this->year == date("Y")) ? time() : mktime(1, 0, 0, 12, 31, $this->year);
			$maxW = ($starttime - mktime(1, 0, 0, 12, 31, $this->year-1))/(7*DAY_IN_S);
		}

		$CompleteData   = array();
		$CurrentWeekEnd = Time::Weekend($starttime);
		$CompleteResult = $Dataset->getGroupOfTrainingsForTimerange($this->sportid, 7*DAY_IN_S, $CurrentWeekEnd - ($maxW+2)*7*DAY_IN_S, $CurrentWeekEnd);

		foreach ($CompleteResult as $Data) {
			$CompleteData[$Data['timerange']] = $Data;
		}

		for ($w = 0; $w <= $maxW; $w++) {
			$time  = $starttime - $w*7*DAY_IN_S;
			$start = Time::Weekstart($time);
			$end   = Time::Weekend($time);
			$week  = 'KW '.date('W', $time);

			echo '<tr class="a'.(($w%2)+1).'"><td class="b l" title="'.date("d.m.Y", $start).' bis '.date("d.m.Y", $end).'">'.DataBrowser::getLink($week, $start, $end).'</td>';

			if (isset($CompleteData[$w]) && $Dataset->setGroupOfTrainings($CompleteData[$w])) {
				if (isset($CompleteData[$w+1]))
					$Dataset->setKilometerToCompareTo($CompleteData[$w+1]['distance']);

				$Dataset->displayTableColumns();
			} else
				echo HTML::emptyTD($Dataset->column_count, '<em>keine Trainings</em>', 'c');

			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display days of streakrunning 
	 */
	private function displayStreak() {
		$Mysql    = Mysql::getInstance();
		$Result   = $Mysql->query('SELECT time,DATE(FROM_UNIXTIME(time)) as day FROM '.PREFIX.'training WHERE `sportid`='.CONF_RUNNINGSPORT.' GROUP BY DATE(FROM_UNIXTIME(time)) ORDER BY day DESC');
		$IsStreak = true;
		$FirstDay = true;
		$NumDays  = 0;
		$LastTime = time();
		$LastDay  = date('Y-m-d');
		$Text     = '';

		while ($IsStreak) {
			$Day = mysql_fetch_assoc($Result);

			if ($FirstDay) {
				if ($Day['day'] != $LastDay) {
					if (Time::diffOfDates($Day['day'], $LastDay) == 1) {
						$Text = 'Wenn du heute noch l&auml;ufst: ';
						$NumDays++;
					} else
						$IsStreak = false;
				}

				$FirstDay = false;
			}

			if (!$Day || !$IsStreak)
				$IsStreak = false;
			else {
				if (Time::diffOfDates($Day['day'], $LastDay) <= 1) {
					$NumDays++;
					$LastDay  = $Day['day'];
					$LastTime = $Day['time'];
				} else {
					$IsStreak = false;
				}
			}
		}

		if ($NumDays == 0) {
			$Text .= 'Du hast derzeit keinen Streak.';
			$LastTraining = $Mysql->fetchSingle('SELECT time FROM '.PREFIX.'training WHERE `sportid`='.CONF_RUNNINGSPORT.' ORDER BY time DESC');

			if (isset($LastTraining['time']))
				$Text .= ' Dein letzter Lauf war am '.date('d.m.Y', $LastTraining['time']);
		} else {
			$Text .= $NumDays.' Tag'.($NumDays == 1 ? '' : 'e').' laufen seit dem '.date('d.m.Y', $LastTime);
		}

		if ($NumDays >= $this->config['show_streak_days']['var'])
			echo '<p class="text c"><em>'.$Text.'</em></p>';
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		$this->sport = Mysql::getInstance()->fetch(PREFIX.'sport', $this->sportid);

		if ($this->year != -1) {
			$this->num = 12;
			$this->num_start = 1;
			$this->num_end   = 12;
		} else {
			$this->num = date("Y") - START_YEAR + 1;
			$this->num_start = START_YEAR;
			$this->num_end   = date("Y");
		}

		$this->colspan = $this->num + 1;
	}

	/**
	 * Initialize all line-data-arrays
	 */
	private function initLineData() {
		$this->initCompleteData();

		foreach ($this->CompleteData as $Data) {
			$this->initStundenData($Data);
			$this->initKMData($Data);
			$this->initTempoData($Data);
			$this->initVDOTData($Data);
			$this->initTRIMPData($Data);
		}
	}

	private function initCompleteData() {
		$Query = '
			SELECT
				SUM(`s`) as `s`,
				SUM(`distance`) as `distance`,
				AVG(NULLIF(`vdot`,0)) as `vdot`,
				SUM(`trimp`) as `trimp`,
				'.($this->year!=-1?'MONTH':'YEAR').'(FROM_UNIXTIME(`time`)) as `i`
			FROM
				`'.PREFIX.'training`
			WHERE
				`sportid`='.$this->sportid;
		$Query .= ($this->year != -1)
			? ' && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' GROUP BY MONTH(FROM_UNIXTIME(`time`)) ORDER BY `i` LIMIT 12'
			: ' GROUP BY YEAR(FROM_UNIXTIME(`time`)) ORDER BY `i`';

		$this->CompleteData = Mysql::getInstance()->fetchAsArray($Query);
	}

	/**
	 * Initialize line-data-array for 'Stunden'
	 * @param array $dat
	 */
	private function initStundenData($dat) {
		$text = ($dat['s'] == 0) ? '&nbsp;' : Time::toString($dat['s'], false);
		$this->StundenData[] = array('i' => $dat['i'], 'text' => $text);
	}

	/**
	 * Initialize line-data-array for 'KM'
	 * @param array $dat
	 */
	private function initKMData($dat) {
		$WeekFactor  = 52;
		$MonthFactor = 12;

		if ($dat['i'] == date("Y")) {
			$WeekFactor  = (date('n') == 12 && date('W') < 10) ? 52 : date("W");
			$MonthFactor = date("n");
		} elseif ($dat['i'] == START_YEAR && date("0", START_TIME) == START_YEAR) {
			$WeekFactor  = 53 - date("W", START_TIME);
			$MonthFactor = 13 - date("n", START_TIME);
		}

		$text        = ($dat['distance'] == 0) ? '&nbsp;' : Running::Km($dat['distance'], 0);
		$textWeek    = ($dat['distance'] == 0) ? '&nbsp;' : Running::Km($dat['distance']/$WeekFactor, 0);
		$textMonth   = ($dat['distance'] == 0) ? '&nbsp;' : Running::Km($dat['distance']/$MonthFactor, 0);
		$this->KMData[]      = array('i' => $dat['i'], 'text' => $text);
		$this->KMDataWeek[]  = array('i' => $dat['i'], 'text' => $textWeek);
		$this->KMDataMonth[] = array('i' => $dat['i'], 'text' => $textMonth);
	}

	/**
	 * Initialize line-data-array for 'Tempo'
	 * @param array $dat
	 */
	private function initTempoData($dat) {
		$text = ($dat['s'] == 0) ? '&nbsp;' : Running::Speed($dat['distance'], $dat['s'], $this->sportid);
		$this->TempoData[] = array('i' => $dat['i'], 'text' => $text);
	}

	/**
	 * Initialize line-data-array for 'VDOT'
	 * @param array $dat
	 */
	private function initVDOTData($dat) {
		$VDOT = isset($dat['vdot']) ? JD::correctVDOT($dat['vdot']) : 0;
		$text = ($VDOT == 0) ? '&nbsp;' : number_format($VDOT, 1);

		$this->VDOTData[] = array('i' => $dat['i'], 'text' => $text);
	}

	/**
	 * Initialize line-data-array for 'TRIMP'
	 * @param array $dat
	 */
	private function initTRIMPData($dat) {
		$avg_num = ($this->year != -1) ? 15 : 180;
		$text = ($dat['trimp'] == 0)
			? '&nbsp;'
			: '<span style="color:#'.Running::Stresscolor($dat['trimp']/$avg_num).'">'.$dat['trimp'].'</span>';
		$this->TRIMPData[] = array('i' => $dat['i'], 'text' => $text);
	}
}