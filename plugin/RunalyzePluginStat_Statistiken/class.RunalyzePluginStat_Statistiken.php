<?php
/**
 * This file contains the class::RunalyzePluginStat_Statistiken
 * @package Runalyze\Plugins\Stats
 */

use Runalyze\Configuration;
use Runalyze\Context as GeneralContext;
use Runalyze\Calculation\JD;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Pace;
use Runalyze\Activity\HeartRate;
use Runalyze\Activity\StrideLength;
use Runalyze\View\Stresscolor;
use Runalyze\Data\Weather\Temperature;


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
	 * Data array for displaying the rows
	 * @var array
	 */
	private $LineData = array();

	/**
	 * Dataset for converting and showing data
	 * @var object
	 */
	private $Dataset;

	/**
	 * Data of the Dataset, Dataset->getData()
	 * @var array
	 */
	private $DatasetData = array();

	/**
	 * Info about selected sport
	 * @var boolean
	 */
	private $isRunning;

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
		$LinkList  = '<li class="with-submenu"><span class="link">'.__('Choose statistic').'</span><ul class="submenu">';
		$LinkList .= '<li'.('' == $this->dat ? ' class="active"' : '').'>'.$this->getInnerLink(__('General overview'), $this->sportid, $this->year, '').'</li>';
		$LinkList .= '<li'.('allWeeks' == $this->dat ? ' class="active"' : '').'>'.$this->getInnerLink(__('All training weeks'), $this->sportid, (!$this->showsSpecificYear()) ? date('Y') : $this->year, 'allWeeks').'</li>';

		$LinkList .= '</ul></li>';

		$this->setToolbarNavigationLinks(array($LinkList));
	}


	/**
	 * Init some class variables
	 */
	private function initVariables() {
		$this->Dataset = new Dataset(SessionAccountHandler::getId());
		$this->DatasetData = $this->Dataset->getData();

		$this->isRunning = ($this->sportid == Configuration::General()->runningSport());
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {

		if (!$this->showsSpecificYear() && $this->dat = 'allWeeks') {
			$this->dat = '';
		}

		$this->initVariables();
		$this->initData();
		$this->initLineData();

		$this->setSportsNavigation();
		$this->setYearsNavigation(true, true, true);
		$this->setOwnNavigation();

		$this->setHeader($this->sport['name'].': '.$this->getYearString());

	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p(
			__('This plugin shows summaries for all weeks, months or years to compare your overall training '.
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
		$this->initVariables();
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
		$this->displayTableHeadForTimeRange();
		echo '</thead>';

		echo '<tbody>';

		$DatasetLabels = new DatasetLabels();

		$this->displayLine(__('Activities'), 'number');
		$this->displayLine($DatasetLabels->get('s'), 's');
		$this->displayLine($DatasetLabels->get('distance'), 'distance');

		if ($this->year == -1 && $this->isRunning) {
			$this->displayLine('&oslash;'.NBSP.__('km/Week'), 'distance_week', 'small');
			$this->displayLine('&oslash;'.NBSP.__('km/Month'), 'distance_month', 'small');
		}

		$this->displayLine('&oslash;'.NBSP.__('Pace'), 'pace', 'small');

		if ($this->isRunning) {
			$this->displayLine($DatasetLabels->get('vdot'), 'vdot', 'small');
			$this->displayLine($DatasetLabels->get('jd_intensity'), 'jd_intensity', 'small');
		}

		$this->displayLine($DatasetLabels->get('trimp'), 'trimp', 'small');

		$this->displayLine('&oslash;'.NBSP.__('Cadence'), 'cadence', 'small');
		if ($this->isRunning) {
			$this->displayLine('&oslash;'.NBSP.__('Stride length'), 'stride_length', 'small');
			$this->displayLine('&oslash;'.NBSP.__('Ground contact time'), 'groundcontact', 'small');
			$this->displayLine('&oslash;'.NBSP.__('Vertical oscillation'), 'vertical_oscillation', 'small');
		}

		$this->displayLine($DatasetLabels->get('elevation'), 'elevation', 'small');
		$this->displayLine($DatasetLabels->get('kcal'), 'kcal', 'small');
		$this->displayLine($DatasetLabels->get('pulse_avg'), 'pulse_avg', 'small');
		$this->displayLine($DatasetLabels->get('pulse_max'), 'pulse_max', 'small');
		$this->displayLine($DatasetLabels->get('power'), 'power', 'small');
		$this->displayLine($DatasetLabels->get('temperature'), 'temperature', 'small');
		$this->displayLine($DatasetLabels->get('abc'), 'abc', 'small');

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display one statistic line
	 * @param string $title
	 * @param array $data Array containing all $data[] = array('i' => i, 'text' => '...')
	 * @param string $class [optional] additional class for table cells
	 */
	private function displayLine($title, $dataname, $class = '') {
		if (!array_key_exists ($dataname, $this->LineData))
			return;

		$data = $this->LineData[$dataname];
		$emptyDataLine = true;
		foreach ($data as $dat) {
			if ($dat['text'] != NBSP) {
				$emptyDataLine = false;
				break;
			}
		}

		if (!$emptyDataLine) {
			echo '<tr>';
			echo '<td class="b">'.$title.'</td>';

			$td_i = 0;
			foreach ($data as $dat) {
				for (; ($this->num_start + $td_i) < $dat['i']; $td_i++)
					echo HTML::emptyTD();
				$td_i++;

				echo '<td'.(!empty($class) ? ' class="'.$class.'"' : '').'>'.$dat['text'].'</td>'.NL;
			}

			for (; $td_i < $this->num; $td_i++)
				echo HTML::emptyTD();

			echo '</tr>';
		}
	}

	/**
	 * Display table with last week-statistics 
	 * @param bool $showAllWeeks
	 */
	private function displayWeekTable($showAllWeeks = false) {
		if (($this->showsAllYears() || ($this->showsSpecificYear() && $this->year != date('Y'))) && !$showAllWeeks)
			return;

		if ($this->Configuration()->value('compare_weeks'))
			$this->Dataset->activateKilometerComparison();

		$title = $showAllWeeks ? __('All training weeks') : __('Last 10 training weeks');

		echo '<table class="r fullwidth zebra-style">';
		echo '<thead><tr><th colspan="'.($this->Dataset->cols()+1).'">'.$title.'</th></tr></thead>';
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
		$CompleteResult = $this->Dataset->getGroupOfTrainingsForTimerange($this->sportid, 7*DAY_IN_S, $CurrentWeekEnd - ($maxW+2)*7*DAY_IN_S, $CurrentWeekEnd);

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

			if (isset($CompleteData[$w]) && !empty($CompleteData[$w])) {
				$this->Dataset->setGroupOfTrainings($CompleteData[$w]);

				if (isset($CompleteData[$w+1])) {
					$this->Dataset->setKilometerToCompareTo($CompleteData[$w+1]['distance']);
				}

				$this->Dataset->displayTableColumns();
			} else
				echo HTML::emptyTD($this->Dataset->cols(), '<em>'.__('No activities').'</em>', 'c small');

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
			WHERE `sportid`='.Configuration::General()->runningSport().' AND `accountid`='.SessionAccountHandler::getId().'
			GROUP BY DATE(FROM_UNIXTIME(`time`))
			ORDER BY `day` DESC';

		$Request = DB::getInstance()->query($Query);

		$IsStreak = true;
		$FirstDay = true;
		$NumDays  = 0;
		$LastTime = time();
		$LastDay  = date('Y-m-d');
		$Text = '';

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

		if ($NumDays >= $this->Configuration()->value('show_streak_days')) {
			if ($NumDays == 0) {
				$Text .= __('You don\'t have a streak. Go out and start one!');
				$LastTraining = DB::getInstance()->query('SELECT time FROM `'.PREFIX.'training` WHERE `sportid`='.Configuration::General()->runningSport().' AND accountid = '.SessionAccountHandler::getId().' ORDER BY `time` DESC LIMIT 1')->fetch();

				if (isset($LastTraining['time']))
					$Text .= ' '.sprintf( __('Your last run was on %s'), date('d.m.Y', $LastTraining['time']));
			} else {
				$Text .= sprintf( _n('%d day of running since %s', '%d days of running since %s', $NumDays), $NumDays, date('d.m.Y', $LastTime) );
			}

			echo '<p class="text c"><em>'.$Text.'</em></p>';
		}
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		$this->sport = SportFactory::DataFor($this->sportid);

		if ($this->year != -1) {
			$this->num_start = 1;
			$this->num_end   = 12;
		} else {
			$this->num_start = START_YEAR;
			$this->num_end   = date("Y");
		}
		$this->num = $this->num_end - $this->num_start + 1;

		$this->colspan = $this->num + 1;
	}

	/**
	 * Initialize all line-data-arrays
	 */
	private function initLineData() {
		$this->initCompleteData();
		$this->initTotalData();

		foreach ($this->CompleteData as $Data) {
			$Data['sportid'] = $this->sportid;
			$this->Dataset->setActivityData($Data);
			foreach ($this->DatasetData as $set) {

				$text = NBSP;
				switch ($set['name']) {
					case 'abc':
						if ($Data['abc'] > 0)
							$text = $Data['abc'].'x';
						break;

					case 'distance':
						$WeekFactor  = 52;
						$MonthFactor = 12;
				
						if ($Data['i'] == date("Y")) {
							$WeekFactor  = (date('z')+1) / 7;
							$MonthFactor = (date('z')+1) / 30.4;
						} elseif ($Data['i'] == date('Y') + 1) {
							$WeekFactor = ceil( (time() - START_TIME) / DAY_IN_S / 7 );
							$MonthFactor = ceil( (time() - START_TIME) / DAY_IN_S / 30.4 );
						} elseif ($Data['i'] == START_YEAR && date("Y", START_TIME) == START_YEAR) {
							$WeekFactor  = 53 - date("W", START_TIME);
							$MonthFactor = 13 - date("n", START_TIME);
						}
				
						$text        = ($Data['distance'] == 0) ? NBSP : Distance::format($Data['distance'], false, 0);
						$textWeek    = ($Data['distance'] == 0) ? NBSP : Distance::format($Data['distance']/$WeekFactor, false, 0);
						$textMonth   = ($Data['distance'] == 0) ? NBSP : Distance::format($Data['distance']/$MonthFactor, false, 0);
						$this->LineData['distance_week'][]  = array('i' => $Data['i'], 'text' => $textWeek);
						$this->LineData['distance_month'][] = array('i' => $Data['i'], 'text' => $textMonth);
						break;

					/* use this as long as
					 * https://github.com/Runalyze/Runalyze/blob/1c66c261bb0d625fd368cf475122f658a805304c/inc/class.Dataset.php#L442
					 * is not fixed
					 */
					case 'pace':
						$Pace = new Pace($Data['s_sum_with_distance'], $Data['distance'], SportFactory::getSpeedUnitFor($this->sportid));
						if ($Data['s_sum_with_distance'] != 0)
							$text = $Pace->valueWithAppendix();
						break;

					case 'stride_length':
						if ($Data['steps'] > 0) {
							$strideLength = new StrideLength ($Data['distance_sum_with_cadence']*50000/$Data['steps']);
							if ($strideLength->inCM() > 0)
								$text = $strideLength->string();
						}
						break;

					case 'vdot':
						$VDOT = isset($Data['vdot']) ? Configuration::Data()->vdotFactor()*($Data['vdot']) : 0;
						if ($VDOT > 0)
							$text = number_format($VDOT, 1);
						break;

					case 'jd_intensity':
						$avg  = ($this->year != -1) ? 8 : 100;

						if ($Data['jd_intensity'] == 0) {
							$text = NBSP;
						} else {
							$Stress = new Stresscolor($Data['jd_intensity'] / $avg);
							$Stress->scale(0, 50);
							$text = $Stress->string($Data['jd_intensity']);
						}
						break;


					default:
						$DataString = $this->Dataset->getDataset($set['name']);
						$text = (($DataString == '' or $DataString == 0) ? NBSP : $DataString);
						break;
				}

				$this->LineData[$set['name']][] = array ('i' => $Data['i'], 'text' => $text);

			}
			// handling 'number' separately, as it's not in the Dataset->getData() array
			$text = $Data['number'] > 0 ? $Data['number'] : NBSP;
			$this->LineData['number'][] = array('i' => $Data['i'], 'text' => $text);
		}
			
	}

	/**
	 * Init complete data
	 */
	private function initCompleteData() {
		$Query = 
			'SELECT '
				.'COUNT(`id`) as `number`, '
				.'SUM(IF(`distance`>0,`s`,0)) as `s_sum_with_distance`, '
				.'SUM(IF(`cadence`>0,`distance`,0)) as `distance_sum_with_cadence`, '
				.'SUM(`s`*`cadence`/60) as `steps`, '
				.$this->getTimerIndexForQuery().' as `i`, '
				.'SUM(`abc`) as `abc` '
				.$this->Dataset->getQuerySelectForSet().' '
			.'FROM '
				.'`'.PREFIX.'training` '
			.'WHERE '
				.'`accountid`=:sessid '.$this->getSportAndYearDependenceForQuery();

		$Query .= ' GROUP BY '.$this->getTimerForOrderingInQuery().' ASC';

		$Request = DB::getInstance()->prepare($Query);
		$Request->bindValue('sessid', SessionAccountHandler::getId(), PDO::PARAM_INT);

		$Request->execute();

		$this->CompleteData = $Request->fetchAll();
	}


	private function helperComputeAverage($totalvalue, $totalcount) {
		return ($totalcount > 0 ? $totalvalue / $totalcount : $totalvalue);
	}

	private function initTotalData() {

		if ($this->year == -1) {
			$Query = 
				'SELECT '
					.'COUNT(`id`) as `number`, '
					.'SUM(IF(`distance`>0,`s`,0)) as `s_sum_with_distance`, '
					.'SUM(IF(`cadence`>0,`distance`,0)) as `distance_sum_with_cadence`, '
					.'SUM(`s`*`cadence`/60) as `steps`, '
					.'SUM(`abc`) as `abc` '
					.$this->Dataset->getQuerySelectForSet().' '
				.'FROM '
					.'`'.PREFIX.'training` '
				.'WHERE '
					.'`accountid`=:sessid '.$this->getSportAndYearDependenceForQuery();
	
			$Request = DB::getInstance()->prepare($Query);
			$Request->bindValue('sessid', SessionAccountHandler::getId(), PDO::PARAM_INT);
	
			$Request->execute();
	
			$Total = $Request->fetch();
			$Total['i'] = date('Y') + 1;
	
			$this->CompleteData[] = $Total;
		}
	}

}
