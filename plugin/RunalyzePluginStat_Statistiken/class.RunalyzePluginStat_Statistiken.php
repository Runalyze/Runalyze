<?php
/**
 * This file contains the class::RunalyzePluginStat_Statistiken
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Statistiken';
/**
 * Plugin "Statistiken"
 * 
 * General statistics
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Statistiken extends PluginStat {
	/**
	 * Sport
	 * @var array
	 */
	private $sport  = array();

	/**
	 * Colspan
	 * @var int
	 */
	private $colspan = 0;

	/**
	 * Number of datasets
	 * @var int
	 */
	private $num = 0;

	/**
	 * Index of first dataset
	 * @var int
	 */
	private $num_start = 0;

	/**
	 * Index of last dataset
	 * @var int
	 */
	private $num_end = 0;

	/**
	 * Complete data
	 * @var array
	 */
	private $CompleteData = array();

	/**
	 * Data for hours
	 * @var array
	 */
	private $StundenData = array();

	/**
	 * Data for kilometer
	 * @var array
	 */
	private $KMData = array();

	/**
	 * Kilometer data for week
	 * @var array
	 */
	private $KMDataWeek = array(); // = KMData / 52

	/**
	 * Kilometer data for month
	 * @var array
	 */
	private $KMDataMonth = array(); // = KMData / 12

	/**
	 * Data for pace
	 * @var array
	 */
	private $TempoData = array();

	/**
	 * Data for vdot
	 * @var array
	 */
	private $VDOTData = array();

	/**
	 * Data for JD intensity
	 * @var array
	 */
	private $JDIntensityData = array();

	/**
	 * Data for trimp
	 * @var array
	 */
	private $TRIMPData = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Statistics');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Monthly and weekly summaries for all sports');
	}

	protected function setOwnNavigation() {
		$LinkList  = '<li class="with-submenu"><span class="link">'.__('All training weeks').'</span><ul class="submenu">';

		for ($x = date("Y"); $x >= START_YEAR; $x--)
			$LinkList .= '<li>'.$this->getInnerLink($x, $this->sportid, $x, 'allWeeks').'</li>';

		$LinkList .= '</ul></li>';

		$this->setToolbarNavigationLinks(array($LinkList));
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

		$this->setHeader($this->sport['name'].': '.$this->getYearString());
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p(
			__('This plugin shows summaries for all weeks, months or years to compare your overall training'.
				'in terms of time, distance, pace, VDOT and TRIMP.')
		);
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue( new PluginConfigurationValueBool('compare_weeks', __('Compare kilometers per week'), '', true) );
		$Configuration->addValue( new PluginConfigurationValueBool('show_streak', __('Show streak'), '', true) );
		$Configuration->addValue( new PluginConfigurationValueInt('show_streak_days', __('Minimum number of days to show a streak (0 for always)'), '', 10) );

		$this->setConfiguration($Configuration);
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		if ($this->wantToShowAllWeeks()) {
			$this->displayWeekTable(true);
		} else {
			$this->displayYearTable();

			if ($this->Configuration()->value('show_streak'))
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
	 * Get table for year comparison - not to use within this plugin!
	 * @return string
	 */
	public function getYearComparisonTable() {
		$this->year = -1;
		$this->initData();
		$this->initLineData();

		ob_start();
		$this->displayYearTable();
		return ob_get_clean();
	}

	/**
	 * Display table with data for each month 
	 */
	private function displayYearTable() {
		echo '<table class="r fullwidth zebra-style">';

		echo '<thead class="r">';
		echo ($this->year == -1) ? HTML::yearTR(0, 1, 'th') : HTML::monthTR(8, 1, 'th');
		echo '</thead>';
		echo '<tbody>';

		$this->displayLine(__('Time'), $this->StundenData);

		if ($this->sport['distances'] != 0) {
			$this->displayLine(__('Distance'), $this->KMData);
			if ($this->year == -1) {
				$this->displayLine('&oslash;'.NBSP.__('km/Week'), $this->KMDataWeek, 'small');
				$this->displayLine('&oslash;'.NBSP.__('km/Month'), $this->KMDataMonth, 'small');
			}
			$this->displayLine('&oslash;'.NBSP.__('Pace'), $this->TempoData, 'small');
		}

		if ($this->sportid == CONF_RUNNINGSPORT && CONF_RECHENSPIELE)
			$this->displayLine(__('VDOT'), $this->VDOTData, 'small');

		if ($this->sportid == CONF_RUNNINGSPORT && CONF_RECHENSPIELE)
			$this->displayLine(__('JDpoints'), $this->JDIntensityData, 'small');

		if (CONF_RECHENSPIELE)
			$this->displayLine(__('TRIMP'), $this->TRIMPData, 'small');

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display one statistic line
	 * @param string $title
	 * @param array $data Array containing all $data[] = array('i' => i, 'text' => '...')
	 * @param string $class [optional] additional class for table cells
	 */
	private function displayLine($title, $data, $class = '') {
		echo '<tr>';
		echo '<td class="b">'.$title.'</td>';

		if (empty($data)) {
			echo HTML::emptyTD($this->colspan);
		} else {
			$td_i = 0;
			foreach ($data as $dat) {
				for (; ($this->num_start + $td_i) < $dat['i']; $td_i++)
					echo HTML::emptyTD();
				$td_i++;

				echo '<td'.(!empty($class) ? ' class="'.$class.'"' : '').'>'.$dat['text'].'</td>'.NL;
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

		if ($this->Configuration()->value('compare_weeks'))
			$Dataset->activateKilometerComparison();

		echo '<table class="r fullwidth zebra-style">';
		echo '<thead><tr><th colspan="'.($Dataset->cols()+1).'">'.($showAllWeeks?__('All'):__('Last').' 10').' '.__('training weeks').'</th></tr></thead>';
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
			$week  = Icon::$CALENDAR.' '.__('Week').' '.date('W', $time);

			//echo '<tr><td class="b l"">'.DataBrowserLinker::link($week, $start, $end).'</td>';
			echo '<tr><td class="l"><span class="b">'.DataBrowserLinker::link($week, $start, $end, '').'</span>&nbsp;&nbsp;&nbsp;<span class="small">'.date('d.m',$start)." - ".date('d.m',$end).'</span></td>';

			if (isset($CompleteData[$w]) && $Dataset->setGroupOfTrainings($CompleteData[$w])) {
				if (isset($CompleteData[$w+1]))
					$Dataset->setKilometerToCompareTo($CompleteData[$w+1]['distance']);

				$Dataset->displayTableColumns();
			} else
				echo HTML::emptyTD($Dataset->cols(), '<em>'.__('No activities').'</em>', 'c small');

			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display days of streakrunning 
	 */
	private function displayStreak() {
		$Query = '
			SELECT
				`time`,
				DATE(FROM_UNIXTIME(`time`)) as `day`
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.CONF_RUNNINGSPORT.'
			GROUP BY DATE(FROM_UNIXTIME(`time`))
			ORDER BY `day` DESC';

		$Request = DB::getInstance()->prepare($Query);
		$Request->bindValue('sportid', CONF_RUNNINGSPORT);
		$Request->execute();

		$IsStreak = true;
		$FirstDay = true;
		$NumDays  = 0;
		$LastTime = time();
		$LastDay  = date('Y-m-d');
		$Text     = '';

		while ($IsStreak) {
			$Day = $Request->fetch();

			if ($FirstDay) {
				if ($Day['day'] != $LastDay) {
					if (Time::diffOfDates($Day['day'], $LastDay) == 1) {
						$Text = __('If you run today: ');
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
			$Text .= __('You don\'t have a streak. Go out and start one!');
			$LastTraining = DB::getInstance()->query('SELECT time FROM `'.PREFIX.'training` WHERE `sportid`='.CONF_RUNNINGSPORT.' ORDER BY `time` DESC')->fetch();

			if (isset($LastTraining['time']))
				$Text .= sprintf( __('Your last run was on %s'), date('d.m.Y', $LastTraining['time']));
		} else {
			$Text .= sprintf( _n('%d day of running since %s', '%d days of running since %s', $NumDays), $NumDays, date('d.m.Y', $LastTime) );
		}

		if ($NumDays >= $this->Configuration()->value('show_streak_days'))
			echo '<p class="text c"><em>'.$Text.'</em></p>';
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		$this->sport = DB::getInstance()->fetchByID('sport', $this->sportid);

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
			$this->initJDIntensityData($Data);
			$this->initTRIMPData($Data);
		}
	}

	/**
	 * Init complete data
	 */
	private function initCompleteData() {
		$Timer = $this->year != -1 ? 'MONTH' : 'YEAR';

		$Query = '
			SELECT
				SUM(`s`) as `s`,
				SUM(IF(`distance`>0,`s`,0)) as `s_sum_with_distance`,
				SUM(`distance`) as `distance`,
				SUM('.JD::mysqlVDOTsum().')/SUM('.JD::mysqlVDOTsumTime().') as `vdot`,
				SUM(`trimp`) as `trimp`,
				SUM(`jd_intensity`) as `jd_intensity`,
				'.$Timer.'(FROM_UNIXTIME(`time`)) as `i`
			FROM
				`'.PREFIX.'training`
			WHERE
				`sportid`=:sportid ';

		if ($this->year != -1)
			$Query .= '&& YEAR(FROM_UNIXTIME(`time`))=:year ';

		$Query .= 'GROUP BY '.$Timer.'(FROM_UNIXTIME(`time`)) ORDER BY `i`';

		$Request = DB::getInstance()->prepare($Query);
		$Request->bindParam('sportid', $this->sportid, PDO::PARAM_INT);

		if ($this->year != -1)
			$Request->bindParam('year', $this->year, PDO::PARAM_INT);

		$Request->execute();

		$this->CompleteData = $Request->fetchAll();
	}

	/**
	 * Initialize line-data-array for 'Stunden'
	 * @param array $dat
	 */
	private function initStundenData($dat) {
		$text = ($dat['s'] == 0) ? NBSP : Time::toString($dat['s'], false);

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
			$WeekFactor  = (date('z')+1) / 7;
			$MonthFactor = (date('z')+1) / 30.4;
		} elseif ($dat['i'] == START_YEAR && date("0", START_TIME) == START_YEAR) {
			$WeekFactor  = 53 - date("W", START_TIME);
			$MonthFactor = 13 - date("n", START_TIME);
		}

		$text        = ($dat['distance'] == 0) ? NBSP : Running::Km($dat['distance'], 0);
		$textWeek    = ($dat['distance'] == 0) ? NBSP : Running::Km($dat['distance']/$WeekFactor, 0);
		$textMonth   = ($dat['distance'] == 0) ? NBSP : Running::Km($dat['distance']/$MonthFactor, 0);
		$this->KMData[]      = array('i' => $dat['i'], 'text' => $text);
		$this->KMDataWeek[]  = array('i' => $dat['i'], 'text' => $textWeek);
		$this->KMDataMonth[] = array('i' => $dat['i'], 'text' => $textMonth);
	}

	/**
	 * Initialize line-data-array for 'Tempo'
	 * @param array $dat
	 */
	private function initTempoData($dat) {
		$text = ($dat['s_sum_with_distance'] == 0) ? NBSP : SportFactory::getSpeedWithAppendixAndTooltip($dat['distance'], $dat['s_sum_with_distance'], $this->sportid);

		$this->TempoData[] = array('i' => $dat['i'], 'text' => $text);
	}

	/**
	 * Initialize line-data-array for 'VDOT'
	 * @param array $dat
	 */
	private function initVDOTData($dat) {
		$VDOT = isset($dat['vdot']) ? JD::correctVDOT($dat['vdot']) : 0;
		$text = ($VDOT == 0) ? NBSP : number_format($VDOT, 1);

		$this->VDOTData[] = array('i' => $dat['i'], 'text' => $text);
	}

	/**
	 * Initialize line-data-array for 'JD-points'
	 * @param array $dat
	 */
	private function initJDIntensityData($dat) {
		$avg  = ($this->year != -1) ? 8 : 100;
		$text = ($dat['jd_intensity'] == 0) ? NBSP : Running::StresscoloredString($dat['jd_intensity']/$avg, $dat['jd_intensity']);

		$this->JDIntensityData[] = array('i' => $dat['i'], 'text' => $text);
	}

	/**
	 * Initialize line-data-array for 'TRIMP'
	 * @param array $dat
	 */
	private function initTRIMPData($dat) {
		$avg  = ($this->year != -1) ? 15 : 180;
		$text = ($dat['trimp'] == 0) ? NBSP : Running::StresscoloredString($dat['trimp']/$avg, $dat['trimp']);

		$this->TRIMPData[] = array('i' => $dat['i'], 'text' => $text);
	}
}