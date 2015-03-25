<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Rechenspiele".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Rechenspiele';

use Runalyze\Calculation\Performance;
use Runalyze\Calculation\Trimp;
use Runalyze\Calculation\Monotony;
use Runalyze\Configuration;
use Runalyze\Calculation\JD\VDOT;
use Runalyze\Calculation\JD\VDOTCorrector;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\View\Tooltip;

/**
 * Class: RunalyzePluginPanel_Rechenspiele
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Rechenspiele extends PluginPanel {
	/**
	 * @var string
	 */
	const CACHE_KEY_JD_POINTS = 'JDQuery';

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Calculations');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Calculate experimental values as shape and fatigue based on TRIMP, basic endurance and your VDOT shape.');
	}

	/**
	 * Display long description
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('Runalyze uses a lot of tables and derived formulas from Jack Daniels\' Running formula. '.
						'That way Runalyze is able to predict your current VDOT value.') );
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$ShowPaces = new PluginConfigurationValueBool('show_trainingpaces', __('Show: Paces'));
		$ShowPaces->setTooltip( __('Paces based on your curent VDOT') );
		$ShowPaces->setDefaultValue(true);

		$ShowTrimp = new PluginConfigurationValueBool('show_trimpvalues', __('Show: ATL/CTL/TSB'));
		$ShowTrimp->setTooltip( __('Show actual/chronical training load and stress balance (based on TRIMP)') );
		$ShowTrimp->setDefaultValue(true);

		$ShowTrimpExtra = new PluginConfigurationValueBool('show_trimpvalues_extra', __('Show: Monotony/TS'));
		$ShowTrimpExtra->setTooltip( __('Show monotony and training strain (based on TRIMP)') );
		$ShowTrimpExtra->setDefaultValue(true);

		$ShowVDOT = new PluginConfigurationValueBool('show_vdot', __('Show: VDOT'));
		$ShowVDOT->setTooltip( __('Predict current VDOT value') );
		$ShowVDOT->setDefaultValue(true);

		$ShowBE = new PluginConfigurationValueBool('show_basicendurance', __('Show: Basic endurance'));
		$ShowBE->setTooltip( __('Guess current basic endurance') );
		$ShowBE->setDefaultValue(true);

		$ShowJD = new PluginConfigurationValueBool('show_jd_intensity', __('Show: Training points'));
		$ShowJD->setTooltip( __('Training intensity by Jack Daniels') );
		$ShowJD->setDefaultValue(true);

		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue($ShowPaces);
		$Configuration->addValue($ShowTrimp);
		$Configuration->addValue($ShowTrimpExtra);
		$Configuration->addValue($ShowVDOT);
		$Configuration->addValue($ShowBE);
		$Configuration->addValue($ShowJD);

		$this->setConfiguration($Configuration);
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = '';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.plot.php" '.Ajax::tooltip('', __('Show form'), true, true).'>'.Icon::$LINE_CHART.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.php" '.Ajax::tooltip('', __('How are these values calculated?'), true, true).'>'.Icon::$MAGIC.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.info.php" '.Ajax::tooltip('',  __('Explanations: What are VDOT and TRIMP?'), true, true).'>'.Icon::$INFO.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->showValues();

		if ($this->Configuration()->value('show_trainingpaces')) {
			$this->showPaces();
		}

		if (Time::diffInDays(START_TIME) < 70) {
			echo HTML::info( __('There are not enough activities for good calculations.') );
		}
	}

	/**
	 * Show values
	 */
	protected function showValues() {
		$ModelQuery = new Performance\ModelQuery();
		$ModelQuery->execute(DB::getInstance());

		$TSBmodel = new Performance\TSB(
			$ModelQuery->data(),
			Configuration::Trimp()->daysForCTL(),
			Configuration::Trimp()->daysForATL()
		);
		$TSBmodel->calculate();

		$MonotonyQuery = new Performance\ModelQuery();
		$MonotonyQuery->setRange(time()-(Monotony::DAYS-1)*DAY_IN_S, time());
		$MonotonyQuery->execute(DB::getInstance());

		$Monotony = new Monotony($MonotonyQuery->data());
		$Monotony->calculate();

		$VDOT        = Configuration::Data()->vdot();
		$ATLmax      = Configuration::Data()->maxATL();
		$CTLmax      = Configuration::Data()->maxCTL();
		$ModelATLmax = $TSBmodel->maxFatigue();
		$ModelCTLmax = $TSBmodel->maxFitness();

		if ($ModelATLmax > $ATLmax) {
			Configuration::Data()->updateMaxATL($ModelATLmax);
			$ATLmax = $ModelATLmax;
		}

		if ($ModelCTLmax > $CTLmax) {
			Configuration::Data()->updateMaxCTL($ModelCTLmax);
			$CTLmax = $ModelCTLmax;
		}

		$ATLabsolute = $TSBmodel->fatigueAt(0);
		$CTLabsolute = $TSBmodel->fitnessAt(0);
		$TSBabsolute = $TSBmodel->performanceAt(0);
		$TrimpValues = array(
			'ATL'		=> round(100*$ATLabsolute/$ATLmax),
			'ATLstring'	=> Configuration::Trimp()->showInPercent() ? round(100*$ATLabsolute/$ATLmax).'&nbsp;&#37;' : $ATLabsolute,
			'CTL'		=> round(100*$CTLabsolute/$CTLmax),
			'CTLstring'	=> Configuration::Trimp()->showInPercent() ? round(100*$CTLabsolute/$CTLmax).'&nbsp;&#37;' : $CTLabsolute,
			'TSB'		=> round(100*$TSBabsolute/max($ATLabsolute, $CTLabsolute)),
			'TSBstring'	=> Configuration::Trimp()->showTSBinPercent() ? sprintf("%+d", round(100*$TSBabsolute/max($ATLabsolute, $CTLabsolute))).'&nbsp;&#37;' : sprintf("%+d", $TSBabsolute),
		);
		$TSBisPositive = $TrimpValues['TSB'] > 0;

		$maxTrimpToBalanced = ceil($TSBmodel->maxTrimpToBalanced($CTLabsolute, $ATLabsolute));
		$restDays = ceil($TSBmodel->restDays($CTLabsolute, $ATLabsolute));

		$JDQuery = Cache::get(self::CACHE_KEY_JD_POINTS);
		if (is_null($JDQuery)) {
			$JDQueryLastWeek = DB::getInstance()->query('SELECT SUM(`jd_intensity`) FROM `'.PREFIX.'training` WHERE `time`>='.Time::Weekstart(time() - 7*DAY_IN_S).' AND `time`<'.Time::Weekend(time() - 7*DAY_IN_S));
			$JDQueryThisWeek = DB::getInstance()->query('SELECT SUM(`jd_intensity`) FROM `'.PREFIX.'training` WHERE `time`>='.Time::Weekstart(time()).' AND `time`<'.Time::Weekend(time()));
			$JDQuery['LastWeek'] = Helper::Unknown($JDQueryLastWeek->fetchColumn(), 0);
			$JDQuery['ThisWeek'] = Helper::Unknown($JDQueryThisWeek->fetchColumn(), 0);
			Cache::set(self::CACHE_KEY_JD_POINTS, $JDQuery, '600');
		}
		$JDPointsLastWeek = $JDQuery['LastWeek'];
		$JDPointsThisWeek = $JDQuery['ThisWeek'];
		$JDPointsPrognosis = round($JDPointsThisWeek / (7 - (Time::Weekend(time()) - time()) / DAY_IN_S) * 7);

		$Values = array(
			array(
				'show'	=> $this->Configuration()->value('show_vdot'),
				'bars'	=> array(
					new ProgressBarSingle(2*round($VDOT - 30), ProgressBarSingle::$COLOR_BLUE)
				),
				'bar-tooltip'	=> '',
				'value'	=> number_format($VDOT, 2),
				'title'	=> __('VDOT'),
				'small'	=> '',
				'tooltip'	=> __('Current average VDOT')
			),
			array(
				'show'	=> $this->Configuration()->value('show_basicendurance'),
				'bars'	=> array(
					new ProgressBarSingle(BasicEndurance::getConst(), ProgressBarSingle::$COLOR_BLUE)
				),
				'bar-tooltip'	=> '',
				'value'	=> BasicEndurance::getConst().'&nbsp;&#37;',
				'title'	=> __('Basic&nbsp;endurance'),
				'small'	=> '',
				'tooltip'	=> __('<em>Experimental value!</em><br>100 &#37; means: you had enough long runs and kilometers per week to run a good marathon, based on your current VDOT.')
			),
			array(
				'show'	=> $this->Configuration()->value('show_trimpvalues'),
				'bars'	=> array(
					new ProgressBarSingle($TrimpValues['ATL'], ProgressBarSingle::$COLOR_BLUE)
				),
				'bar-tooltip'	=> sprintf( __('Current value: %s<br>Maximal value: %s<br>as percentage: %s &#37;'), $ATLabsolute, $ATLmax, $TrimpValues['ATL']),
				'value'	=> $TrimpValues['ATLstring'],
				'title'	=> __('Fatigue'),
				'small'	=> '(ATL)',
				'tooltip'	=> __('Actual Training Load<br><small>Average training impulse of the last weeks in relation to your maximal value.</small>')
			),
			array(
				'show'	=> $this->Configuration()->value('show_trimpvalues'),
				'bars'	=> array(
					new ProgressBarSingle($TrimpValues['CTL'], ProgressBarSingle::$COLOR_BLUE)
				),
				'bar-tooltip'	=> sprintf( __('Current value: %s<br>Maximal value: %s<br>as percentage: %s &#37;'), $CTLabsolute, $CTLmax, $TrimpValues['CTL']),
				'value'	=> $TrimpValues['CTLstring'],
				'title'	=> __('Fitness&nbsp;level'),
				'small'	=> '(CTL)',
				'tooltip'	=> __('Chronical Training Load<br><small>Average training impulse of the last months in relation to your maximal value.</small>')
			),
			array(
				'show'	=> $this->Configuration()->value('show_trimpvalues'),
				'bars'	=> array(
					new ProgressBarSingle(abs($TrimpValues['TSB'])/2, ($TSBisPositive ? ProgressBarSingle::$COLOR_GREEN : ProgressBarSingle::$COLOR_RED), ($TSBisPositive ? 'right' : 'left'))
				),
				'bar-tooltip'	=> 'TSB = CTL - ATL<br>'.sprintf( __('absolute: %s<br>as percentage: %s &#37;'), $CTLabsolute.' - '.$ATLabsolute.' = '.sprintf("%+d", $TSBabsolute), $TrimpValues['TSB']),
				'value'	=> $TrimpValues['TSBstring'],
				'title'	=> __('Stress&nbsp;Balance'),
				'small'	=> '(TSB)',
				'tooltip'	=> __('Training Stress Balance (= CTL - ATL)<br>&gt; 0: You are relaxing.<br>'.
					'&lt; 0: You are training hard.')
			),
			array(
				'show'	=> $this->Configuration()->value('show_trimpvalues') && !$TSBisPositive,
				'bars'	=> array(
					new ProgressBarSingle(100*$restDays/7, ProgressBarSingle::$COLOR_BLUE)
				),
				'bar-tooltip'	=> '',
				'value'	=> $restDays,
				'title'	=> __('Rest&nbsp;days'),
				'small'	=> '',
				'tooltip'	=> __('Rest days needed to reach TSB = 0')
			),
			array(
				'show'	=> $this->Configuration()->value('show_trimpvalues') && $TSBisPositive,
				'bars'	=> array(
					new ProgressBarSingle(100*$maxTrimpToBalanced/1000, ProgressBarSingle::$COLOR_BLUE)
				),
				'bar-tooltip'	=> '',
				'value'	=> $maxTrimpToBalanced,
				'title'	=> __('Easy&nbsp;TRIMP'),
				'small'	=> '',
				'tooltip'	=> __('Max TRIMP that will still keep you at TSB = 0')
			),
			array(
				'show'	=> $this->Configuration()->value('show_trimpvalues_extra'),
				'bars'	=> array(
					new ProgressBarSingle(
							$Monotony->valueAsPercentage(),
							(
								$Monotony->value() > Monotony::CRITICAL ? ProgressBarSingle::$COLOR_RED
								: $Monotony->value() > Monotony::WARNING ? ProgressBarSingle::$COLOR_ORANGE
								: ProgressBarSingle::$COLOR_GREEN
							)
					)
				),
				'bar-tooltip'	=> 'Monotony = avg(Trimp)/stddev(Trimp)',
				'value'	=> number_format($Monotony->value(), 2),
				'title'	=> __('Monotony'),
				'small'	=> '',
				'tooltip'	=> __('Monotony<br><small>Monotony of your last seven days.<br>Values below 1.5 are preferable.</small>')
			),
			array(
				'show'	=> $this->Configuration()->value('show_trimpvalues_extra'),
				'bars'	=> array(
					new ProgressBarSingle(
							$Monotony->trainingStrainAsPercentage(),
							(
								$Monotony->trainingStrainAsPercentage() >= 75 ? ProgressBarSingle::$COLOR_RED
								: ($Monotony->trainingStrainAsPercentage() >= 50 ? ProgressBarSingle::$COLOR_ORANGE
									: ProgressBarSingle::$COLOR_GREEN)
							)
					)
				),
				'bar-tooltip'	=> 'Training strain = sum(Trimp)*Monotony',
				'value'	=> round($Monotony->trainingStrain()),
				'title'	=> __('Training&nbsp;strain'),
				'small'	=> '',
				'tooltip'	=> __('Training strain<br><small>of your last seven days</small>')
			),
			array(
				'show'	=> $this->Configuration()->value('show_jd_intensity'),
				'bars'	=> array(
					new ProgressBarSingle($JDPointsPrognosis/2, ProgressBarSingle::$COLOR_LIGHT),
					new ProgressBarSingle($JDPointsThisWeek/2, ProgressBarSingle::$COLOR_RED)
				),
				'bar-goal'	=> $JDPointsLastWeek/2,
				'bar-tooltip'	=> sprintf( __('This week: %s training points<br>Prognosis: ca. %s training points<br>Last week: %s training points'), $JDPointsThisWeek, $JDPointsPrognosis, $JDPointsLastWeek ),
				'value'	=> $JDPointsThisWeek,
				'title'	=> __('Training&nbsp;points'),
				'small'	=> '',
				'tooltip'	=> __('Training intensity by Jack Daniels.<br>'.
					'Jack Daniels considers the following levels:<br>'.
					'50 points: Beginner<br>'.
					'100 points: Advanced Runner<br>'.
					'200 points: Pro Runner')
			)
		);

		$this->showTableForValues($Values);
	}

	/**
	 * Show table for values
	 * @param array $Values
	 */
	protected function showTableForValues(&$Values) {
		echo '<table class="fullwidth nomargin">';
		foreach ($Values as $Value) {
			if ($Value['show']) {
				$Label = '<strong>'.$Value['title'].'</strong>&nbsp;<small>'.$Value['small'].'</small>';
				$Text = $Value['tooltip'] != '' ? Ajax::tooltip($Label, $Value['tooltip']) : $Label;

				$ProgressBar = new ProgressBar();
				$ProgressBar->setInline();
				$ProgressBar->setTooltip($Value['bar-tooltip']);

				foreach ($Value['bars'] as &$Bar)
					$ProgressBar->addBar($Bar);

				if (isset($Value['bar-goal']))
					$ProgressBar->setGoalLine($Value['bar-goal']);

				$Progress = $ProgressBar->getCode();

				echo '<tr><td>'.$Text.'</td><td style="width:99%;vertical-align:middle;">'.$Progress.'</td><td class="r">'.$Value['value'].'</td></tr>';
			}
		}
		echo '</table>';
	}

	/**
	 * Show paces
	 */
	protected function showPaces() {
		echo '</div>';
		echo '<div class="panel-content panel-sub-content">';

		echo '<table class="fullwidth nomargin">';

		$Paces = $this->getArrayForPaces();
		$VDOT = new VDOT(Configuration::Data()->vdot());

		foreach ($Paces as $Pace) {
			$DisplayedString = '<strong>'.$Pace['short'].'</strong>';

			echo '<tr>';
			echo '<td>'.Ajax::tooltip($DisplayedString, $Pace['description']).'</td>';
			echo '<td class="r"><em>'.Duration::format($VDOT->paceAt($Pace['limit-high']/100)).'</em> - <em>'.Duration::format($VDOT->paceAt($Pace['limit-low']/100)).'</em>/km</td>';
			echo '</tr>';
		}

		echo '</table>';
	}

	/**
	 * Get array for paces
	 * @return array
	 */
	protected function getArrayForPaces() {
		$Paces = array(
			array( /// Easy pace (by Jack Daniels)
				'short'			=> __('Easy'),
				'description'	=> __('Easy pace running refers to warm-ups, cool-downs and recovery runs.'),
				'limit-low'		=> 59,
				'limit-high'	=> 74
			),
			array( /// Marathon pace (by Jack Daniels)
				'short'			=> __('Marathon'),
				'description'	=> __('Steady run or long repeats (e.g. 2 x 4 miles at marathon pace)'),
				'limit-low'		=> 75,
				'limit-high'	=> 84
			),
			array( /// Threshold pace (by Jack Daniels)
				'short'			=> __('Threshold'),
				'description'	=> __('Steady, prolonged or tempo runs or intermittent runs, also called cruise intervals.'),
				'limit-low'		=> 83,
				'limit-high'	=> 88
			),
			array( /// Interval pace (by Jack Daniels)
				'short'			=> __('Interval'),
				'description'	=> __('Intervals: It takes about two minutes for you to gear up to functioning at VO2max so the ideal duration of an interval is 3-5 minutes each.'),
				'limit-low'		=> 95,
				'limit-high'	=> 100
			),
			array( /// Repetition pace (by Jack Daniels)
				'short'			=> __('Repetition'),
				'description'	=> __('Repetitions are fast, but not necessarily "hard," because work bouts are relatively short and are followed by relatively long recovery bouts.'),
				'limit-low'		=> 105,
				'limit-high'	=> 110
			),
		);

		return $Paces;
	}

	/**
	 * Get fieldset for TRIMP
	 * @return \FormularFieldset
	 */
	public function getFieldsetTRIMP() {
		$ModelQuery = new Performance\ModelQuery();
		$ModelQuery->execute(DB::getInstance());

		$TSBmodel = new Performance\TSB(
			$ModelQuery->data(),
			Configuration::Trimp()->daysForCTL(),
			Configuration::Trimp()->daysForATL()
		);
		$TSBmodel->calculate();

		$maxATL      = Configuration::Data()->maxATL();
		$maxCTL      = Configuration::Data()->maxCTL();
		$ATL         = $TSBmodel->fatigueAt(0);
		$CTL         = $TSBmodel->fitnessAt(0);
		$TrimpValues = array(
			'ATL'	=> round(100*$ATL/$maxATL),
			'CTL'	=> round(100*$CTL/$maxCTL),
			'TSB'	=> $TSBmodel->performanceAt(0)
		);

		$Table = '
			<table class="fullwidth zebra-style">
				<thead>
					<tr>
						<th></th>
						<th>'.__('Name').'</th>
						<th>'.__('in &#37;').'</th>
						<th>'.__('Time range').'</th>
						<th>'.__('&oslash; value/day').'</th>
						<th>'.__('max. value').'</th>
						<th class="small">'.__('Description').'</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="b">ATL</td>
						<td>'.__('Actual Training Load').'</td>
						<td class="c">'.$TrimpValues['ATL'].' &#37;</td>
						<td class="c small">'.sprintf( __('%s days'), Configuration::Trimp()->daysForATL()).'</td>
						<td class="c">'.$ATL.'</td>
						<td class="c">'.$maxATL.'</td>
						<td class="small">'.__('Training impulse over a short period').'</td>
					</tr>
					<tr>
						<td class="b">CTL</td>
						<td>'.__('Chronical Training Load').'</td>
						<td class="c">'.$TrimpValues['CTL'].' &#37;</td>
						<td class="c small">'.sprintf( __('%s days'), Configuration::Trimp()->daysForCTL()).'</td>
						<td class="c">'.$CTL.'</td>
						<td class="c">'.$maxCTL.'</td>
						<td class="small">'.__('Training impulse over a long period').'</td>
					</tr>
					<tr>
						<td class="b">TSB</td>
						<td>'.__('Training Stress Balance').'</td>
						<td class="c">'.$TrimpValues['TSB'].'</td>
						<td colspan="3" class="c">'.$CTL.' - '.$ATL.' = '.$TrimpValues['TSB'].'</td>
						<td class="small">'.__('Current impulse<br>positive: recovery<br>negative: hard training').'
						</td>
					</tr>
				</tbody>
			</table>';

		$Fieldset = new FormularFieldset('ATL/CTL/TSB');
		$Fieldset->addBlock( __('ATL/CTL are based on the TRIMP-concept, which adresses an impulse value to every activity. '.
								'ATL and CTL are averaged values over a given time range.') );
		$Fieldset->addBlock($Table);
		$Fieldset->addInfo( __('see <a href="http://www.netzathleten.de/fitness/richtig-trainieren/item/1481-trainingstagebuch-sinnvoll-oder-nicht" title="Das TRIMP-Konzept">The TRIMP-concept</a> (german, netzathleten.de)') );
		$Fieldset->addInfo( __('see <a href="http://fellrnr.com/wiki/TRIMP">TRIMP</a> and <a href="http://fellrnr.com/wiki/Modeling_Human_Performance#The_TSB_Formulas">Modeling Human Performance</a> on fellrnr.com') );

		return $Fieldset;
	}

	/**
	 * Get fieldset for VDOT
	 * @return \FormularFieldset
	 */
	public function getFieldsetVDOT() {
		$Table = '<table class="fullwidth zebra-style">
				<thead>
					<tr>
						<th colspan="10">'.sprintf( __('VDOT values of the last %s days'), Configuration::Vdot()->days() ).'</th>
					</tr>
				</thead>
				<tbody class="top-and-bottom-border">';

		$Tooltip = new Tooltip('');
		$VDOT = new VDOT(0, new VDOTCorrector(Configuration::Data()->vdotFactor()));
		$VDOTs = DB::getInstance()->query('SELECT `id`,`time`,`distance`,IF(`vdot_with_elevation`>0,`vdot_with_elevation`,`vdot`) as `vdot` FROM `'.PREFIX.'training` WHERE time>='.(time() - Configuration::Vdot()->days()*DAY_IN_S).' AND vdot>0 AND use_vdot=1 ORDER BY time ASC')->fetchAll();

		foreach ($VDOTs as $i => $Data) {
			if ($i%10 == 0)
				$Table .= '<tr>';

			$Tooltip->setText(date('d.m.Y', $Data['time']).': '.Distance::format($Data['distance']));
			$VDOT->setValue($Data['vdot']);

			$Table .= '<td '.$Tooltip->attributes().'>'.Ajax::trainingLink($Data['id'], $VDOT->value()).'</td>';

			if ($i%10 == 9)
				$Table .= '</tr>';
		}

		if (count($VDOTs)%10 != 0)
			$Table .= HTML::emptyTD(10 - count($VDOTs)%10);

		$Table .= '</tbody></table>';

		$Fieldset = new FormularFieldset( __('VDOT') );
		$Fieldset->addBlock( sprintf( __('The VDOT value is the average, weighted by the time, of the VDOT of your activities in the last %s days.'), Configuration::Vdot()->days() ) );
		$Fieldset->addBlock( sprintf( __('Your current VDOT shape: <strong>%s</strong><br>&nbsp;'), Configuration::Data()->vdot() ) );
		$Fieldset->addBlock($Table);
		$Fieldset->addInfo( __('Jack Daniels uses VDOT as a fixed value and not based on the training progress.<br>'.
								'We do instead predict the VDOT from all activities based on the heart rate. '.
								'These formulas are derived from Jack Daniels\' tables as well.') );

		return $Fieldset;
	}

	/**
	 * Get fieldset for basic endurance
	 * @return \FormularFieldset
	 */
	public function getFieldsetBasicEndurance() {
		$BasicEndurance = new BasicEndurance();
		$BasicEndurance->readSettingsFromConfiguration();
		$BEresults = $BasicEndurance->asArray();

		$Strategy  = new RunningPrognosisDaniels;
		$Strategy->setupFromDatabase();
		$Strategy->adjustVDOT(false);
		$Prognosis = new RunningPrognosis;
		$Prognosis->setStrategy($Strategy);

		$GeneralTable = '
			<table class="fullwidth zebra-style">
				<tbody class="top-and-bottom-border">
					<tr>
						<td>'.__('<strong>Current VDOT</strong> <small>(based on heart rate)</small>').'</td>
						<td class="r">'.round(Configuration::Data()->vdot(), 2).'</td>
						<td>&nbsp;</td>
						<td>'.sprintf( __('<strong>Target kilometer per week</strong> <small>(%s weeks)</small>'), round($BasicEndurance->getDaysForWeekKm() / 7)).'</td>
						<td class="r">'.Distance::format($BasicEndurance->getTargetWeekKm(), false, 0).'</td>
						<td class="small">'.sprintf( __('done by %s&#37;'), round(100*$BEresults['weekkm-percentage']) ).'</td>
						<td class="small">(&oslash; '.Distance::format(($BEresults['weekkm-result'] / $BasicEndurance->getDaysForWeekKm() * 7), false, 0).')</td>
						<td class="small">x'.$BasicEndurance->getPercentageForWeekKilometer().'</td>
						<td rowspan="2" class="bottom-spacer b" style="vertical-align:middle;">= '.round($BEresults['percentage']).'&#37;</td>
					</tr>
					<tr>
						<td>'.__('<strong>Marathon time</strong> <small>(optimal)</small>').'</td>
						<td class="r">'.Duration::format($Prognosis->inSeconds(42.195)).'</td>
						<td>&nbsp;</td>
						<td>'.sprintf( __('<strong>Target long run</strong> <small>(%s weeks)</small>'), round($BasicEndurance->getDaysToRecognizeForLongjogs() / 7)).'</td>
						<td class="r">'.Distance::format($BasicEndurance->getRealTargetLongjogKmPerWeek(), false, 0).'</td>
						<td class="small">'.sprintf( __('done by %s&#37;'), round(100*$BEresults['longjog-percentage']) ).'</td>
						<td class="small">('.round($BEresults['longjog-result'], 1).' points)</td>
						<td class="small">x'.$BasicEndurance->getPercentageForLongjogs().'</td>
					</tr>
				</tbody>
			</table>';

		$LongjogTable = '<table class="fullwidth zebra-style c">
				<thead>
					<tr>
						<th>'.__('Date').'*</th>
						<th>'.__('Distance').'</th>
						<th>'.__('Points').'</th>
					</tr>
				</thead>
				<tbody>';

		$IgnoredLongjogs = 0;
		$Longjogs        = DB::getInstance()->query($BasicEndurance->getQuery(0, true))->fetchAll();

		foreach ($Longjogs as $Longjog) {
			if ($Longjog['points'] >= 0.2)
				$LongjogTable .= '<tr>
							<td>'.Ajax::trainingLink($Longjog['id'], date('d.m.Y', $Longjog['time'])).'</td>
							<td>'.Distance::format($Longjog['distance']).'</td>
							<td>'.round($Longjog['points'], 1).' points</td>
						</tr>';
			else
				$IgnoredLongjogs++;
		}

		$LongjogTable .= '<tr class="top-spacer no-zebra">
						<td></td>
						<td></td>
						<td class="b">= '.round($BEresults['longjog-result'], 1).' points</td>
					</tr>
				</tbody>
			</table>';
		$LongjogTable .= '<p class="small">'.sprintf( __('* %s &quot;long&quot; jogs do not show up, '.
														'because they have less than 0.2 points.'), $IgnoredLongjogs).'</p>';
		$LongjogTable .= '<p class="small">'.sprintf( __('* In general, all runs with more than %s are being considered.'),
														Distance::format($BasicEndurance->getMinimalDistanceForLongjogs())).'</p>';

		$Fieldset = new FormularFieldset( __('Basic endurance') );
		$Fieldset->addBlock( __('Your basic endurance is based on your weekly kilometers and your long jogs.<br>'.
								'The target is derived from the possible marathon time based on your current shape.').'<br>&nbsp;' );
		$Fieldset->addBlock($GeneralTable);
		$Fieldset->addBlock( __('The points for your long runs are weighted by time and quadratic in distance. '.
								'That means, a long jog yesterday gives more points than a long jog two weeks ago '.
								'and a 30k-jog gives more points than two 20k-jogs.').'<br>&nbsp;' );
		$Fieldset->addBlock($LongjogTable);
		$Fieldset->addBlock( __('The basic endurance is <strong>not</strong> from Jack Daniels.<br>'.
								'It\'s our own attempt to adjust the prognosis for long distances based on your current endurance.') );

		return $Fieldset;
	}

	/**
	 * Get fieldset for paces
	 * @return \FormularFieldset
	 */
	public function getFieldsetPaces() {
		$Table = '<table class="fullwidth zebra-style">
				<thead>
					<tr>
						<th>'.__('Name').'</th>
						<th class="small">'.__('Pace').'</th>
						<th class="small">'.__('Description').'</th>
					</tr>
				</thead>
				<tbody>';

		$VDOT = new VDOT(Configuration::Data()->vdot());

		foreach ($this->getArrayForPaces() as $Pace) {
			$Table .= '<tr>
						<td class="b">'.$Pace['short'].'</td>
						<td class=""><em>'.Duration::format($VDOT->paceAt($Pace['limit-low']/100)).'</em>&nbsp;-&nbsp;<em>'.Duration::format($VDOT->paceAt($Pace['limit-high']/100)).'</em>/km</td>
						<td class="">'.$Pace['description'].'</td>
					</tr>';
		}

		$Table .= '
				</tbody>
			</table>';

		$Fieldset = new FormularFieldset( __('Training paces') );
		$Fieldset->addBlock($Table);
		$Fieldset->addInfo( __('These paces are based on Jack Daniels\' recommendation.') );

		return $Fieldset;
	}
}
