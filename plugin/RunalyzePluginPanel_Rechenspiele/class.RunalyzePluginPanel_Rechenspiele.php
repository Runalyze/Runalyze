<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Rechenspiele".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Rechenspiele';

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Calculation\BasicEndurance;
use Runalyze\Calculation\JD\LegacyEffectiveVO2max;
use Runalyze\Calculation\JD\LegacyEffectiveVO2maxCorrector;
use Runalyze\Calculation\Performance;
use Runalyze\Configuration;
use Runalyze\Sports\Performance\Model\TsbModel;
use Runalyze\Sports\Performance\Monotony;
use Runalyze\Util\LocalTime;
use Runalyze\Util\Time;
use Runalyze\View\Tooltip;

/**
 * Class: RunalyzePluginPanel_Rechenspiele
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Rechenspiele extends PluginPanel {
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
		return __('Calculate experimental values as shape and fatigue based on TRIMP, marathon shape and your VO<sub>2</sub>max shape.');
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$ShowTrimp = new PluginConfigurationValueBool('show_trimpvalues', __('Show: ATL/CTL/TSB'));
		$ShowTrimp->setTooltip( __('Show actual/chronical training load and stress balance (based on TRIMP)') );
		$ShowTrimp->setDefaultValue(true);

		$ShowTrimpExtra = new PluginConfigurationValueBool('show_trimpvalues_extra', __('Show: Monotony/TS'));
		$ShowTrimpExtra->setTooltip( __('Show monotony and training strain (based on TRIMP)') );
		$ShowTrimpExtra->setDefaultValue(true);

		$ShowVDOT = new PluginConfigurationValueBool('show_vo2max', __('Show: VO<sub>2</sub>max shape'));
		$ShowVDOT->setTooltip( __('Predict current effective VO<sub>2</sub>max') );
		$ShowVDOT->setDefaultValue(true);

		$ShowBE = new PluginConfigurationValueBool('show_basicendurance', __('Show: Marathon shape'));
		$ShowBE->setTooltip( __('Guess current marathon shape') );
		$ShowBE->setDefaultValue(true);

		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue($ShowTrimp);
		$Configuration->addValue($ShowTrimpExtra);
		$Configuration->addValue($ShowVDOT);
		$Configuration->addValue($ShowBE);

		$this->setConfiguration($Configuration);
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = '';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.plot.php" '.Ajax::tooltip('', __('Show shape'), true, true).'>'.Icon::$LINE_CHART.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.php" '.Ajax::tooltip('', __('How are these values calculated?'), true, true).'>'.Icon::$MAGIC.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="dashboard/help-calculations" '.Ajax::tooltip('',  __('Explanations: What are VO2max and TRIMP?'), true, true).'>'.Icon::$INFO.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->showValues();

		if (0 == Configuration::Data()->vdot()) {
			if ($this->Configuration()->value('show_vo2max')) {
				echo '<p class="error small">'.$this->getNoEffectiveVO2maxDataError().'</p>';
			}
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

		$TSBmodel = new TsbModel(
			$ModelQuery->data(),
			Configuration::Trimp()->daysForCTL(),
			Configuration::Trimp()->daysForATL()
		);
		$TSBmodel->calculate();

		$MonotonyQuery = new Performance\ModelQuery();
		$MonotonyQuery->setRange(time()-(Monotony::DAYS-1)*DAY_IN_S, time());
		$MonotonyQuery->execute(DB::getInstance());

		$Monotony = new Monotony($MonotonyQuery->data(), 2 * Configuration::Data()->maxATL());
		$Monotony->calculate();

		$EffectiveVO2max = Configuration::Data()->vdot();
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
			'TSB'		=> round(100*$TSBabsolute/max($ATLabsolute, $CTLabsolute,1)),
			'TSBstring'	=> Configuration::Trimp()->showTSBinPercent() ? sprintf("%+d", round(100*$TSBabsolute/max($ATLabsolute, $CTLabsolute))).'&nbsp;&#37;' : sprintf("%+d", $TSBabsolute),
		);
		$TSBisPositive = $TrimpValues['TSB'] > 0;

		$maxTrimpToBalanced = ceil($TSBmodel->maxTrimpToBalanced($CTLabsolute, $ATLabsolute));
		$restDays = ceil($TSBmodel->restDays($CTLabsolute, $ATLabsolute));

		$Values = array(
			array(
				'show'	=> $this->Configuration()->value('show_vo2max'),
				'bars'	=> array(
					new ProgressBarSingle(2*round($EffectiveVO2max - 30), ProgressBarSingle::$COLOR_BLUE)
				),
				'bar-tooltip'	=> '',
				'value'	=> number_format($EffectiveVO2max, 2).(0 == $EffectiveVO2max ? '&nbsp;<i class="fa fa-fw fa-exclamation-circle"></i>' : ''),
				'title'	=> __('Effective&nbsp;VO<sub>2</sub>max'),
				'small'	=> '',
				'tooltip'	=> ''
			),
			array(
				'show'	=> $this->Configuration()->value('show_basicendurance'),
				'bars'	=> array(
					new ProgressBarSingle(BasicEndurance::getConst(), ProgressBarSingle::$COLOR_BLUE)
				),
				'bar-tooltip'	=> '',
				'value'	=> BasicEndurance::getConst().'&nbsp;&#37;'.(0 == $EffectiveVO2max ? '&nbsp;<i class="fa fa-fw fa-exclamation-circle"></i>' : ''),
				'title'	=> __('Marathon&nbsp;shape'),
				'small'	=> '',
				'tooltip'	=> __('<em>Experimental value!</em><br>100 &#37; means: you had enough long runs and kilometers per week to run an optimal marathon based on your current shape.')
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

				echo '<tr><td>'.$Text.'</td><td style="width:99%;vertical-align:middle;">'.$Progress.'</td><td class="r nowrap">'.$Value['value'].'</td></tr>';
			}
		}
		echo '</table>';
	}

	/**
	 * Get fieldset for TRIMP
	 * @return \FormularFieldset
	 */
	public function getFieldsetTRIMP() {
		$ModelQuery = new Performance\ModelQuery();
		$ModelQuery->execute(DB::getInstance());

		$TSBmodel = new TsbModel(
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
						<th>'.__('avg.').' '.__('value/day').'</th>
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
	 * @return \FormularFieldset
	 */
	public function getFieldsetEffecticeVO2max() {
		$Tooltip = new Tooltip('');
		$EffectiveVO2max = new LegacyEffectiveVO2max(0, new LegacyEffectiveVO2maxCorrector(Configuration::Data()->vdotFactor()));
		$vdotColumn = Configuration::Vdot()->useElevationCorrection() ? 'IF(`vdot_with_elevation`>0,`vdot_with_elevation`,`vdot`) as `vdot`' : '`vdot`';
		$EffectiveVO2maxValues = DB::getInstance()->query('SELECT `id`,`time`,`distance`,'.$vdotColumn.' FROM `'.PREFIX.'training` WHERE time>='.(time() - Configuration::Vdot()->days()*DAY_IN_S).' AND vdot>0 AND use_vdot=1 AND accountid = '.SessionAccountHandler::getId().' ORDER BY time ASC')->fetchAll();

		if (empty($EffectiveVO2maxValues)) {
			$Table = '<p class="error">'.$this->getNoEffectiveVO2maxDataError().'</p>';
		} else {
			$Table = '<table class="fullwidth zebra-style">
				<thead>
					<tr>
						<th colspan="10">'.sprintf( __('Effective VO<sub>2</sub>max values of the last %s days'), Configuration::Vdot()->days() ).'</th>
					</tr>
				</thead>
				<tbody class="top-and-bottom-border">';

			foreach ($EffectiveVO2maxValues as $i => $Data) {
				if ($i % 10 == 0)
					$Table .= '<tr>';

				$Tooltip->setText((new LocalTime($Data['time']))->format('d.m.Y').': '.Distance::format($Data['distance']));
                $EffectiveVO2max->setValue($Data['vdot']);

				$Table .= '<td '.$Tooltip->attributes().'>'.Ajax::trainingLink($Data['id'], $EffectiveVO2max->value()).'</td>';

				if ($i % 10 == 9)
					$Table .= '</tr>';
			}

			if (count($EffectiveVO2maxValues) % 10 != 0)
				$Table .= HTML::emptyTD(10 - count($EffectiveVO2maxValues) % 10);

			$Table .= '</tbody></table>';
		}

		$Fieldset = new FormularFieldset( __('Effective VO<sub>2</sub>max') );
		$Fieldset->addBlock( sprintf( __('The VO<sub>2</sub>max shape is calculated as the average, weighted by time, of estimated VO<sub>2</sub>max values of your activities in the last %s days.'), Configuration::Vdot()->days() ) );
		$Fieldset->addBlock( sprintf( __('Your current VO<sub>2</sub>max shape: <strong>%s</strong><br>&nbsp;'), Configuration::Data()->vdot() ) );
		$Fieldset->addBlock($Table);
		$Fieldset->addInfo( __('VO<sub>2</sub>max itself is a scientific metric for the maximal oxygen consumption that can be measured in the laboratory.<br>'.
								'Two runners with equal VO<sub>2</sub>max values do not need to perform equally, as running efficiency plays an additional role. '.
								'To overcome the issue of specifying running efficiency, one can ignore the efficiency and therefore call it effective VO<sub>2</sub>max.') );

		return $Fieldset;
	}

	/**
	 * Get fieldset for Marathon shape
	 * @return \FormularFieldset
	 */
	public function getFieldsetBasicEndurance() {
		$BasicEndurance = new BasicEndurance();
		$BasicEndurance->readSettingsFromConfiguration();
		$usedVO2max = $BasicEndurance->getUsedEffectiveVO2max();
		$BEresults = $BasicEndurance->asArray();

		$Prognosis = new \Runalyze\Sports\Running\Prognosis\VO2max($usedVO2max);

		$GeneralTable = '
			<table class="fullwidth zebra-style">
				<tbody class="top-and-bottom-border">
					<tr>
						<td><strong>'.__('Current Effective VO<sub>2</sub>max').'</strong> <small>('.__('based on heart rate').')</small></td>
						<td class="r">'.round(Configuration::Data()->vdot(), 2).'</td>
						<td>&nbsp;</td>
						<td><strong>'.__('Target kilometer per week').'</strong> <small>('.sprintf('%s weeks', round($BasicEndurance->getDaysForWeekKm() / 7)).')</small></td>
						<td class="r">'.Distance::format($BasicEndurance->getTargetWeekKm(), false, 0).'</td>
						<td class="small">'.sprintf( __('done by %s&#37;'), round(100*$BEresults['weekkm-percentage']) ).'</td>
						<td class="small">('.__('avg.').' '.Distance::format(($BEresults['weekkm-result'] / $BasicEndurance->getDaysForWeekKm() * 7), false, 0).')</td>
						<td class="small">x'.$BasicEndurance->getPercentageForWeekKilometer().'</td>
						<td rowspan="2" class="bottom-spacer b" style="vertical-align:middle;">= '.round($BEresults['percentage']).'&#37;</td>
					</tr>
					<tr>
						<td>'.__('<strong>Marathon time</strong> <small>(optimal)</small>').'</td>
						<td class="r">'.Duration::format($Prognosis->getSeconds(42.195)).'</td>
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
							<td>'.Ajax::trainingLink($Longjog['id'], (new LocalTime($Longjog['time']))->format('d.m.Y')).'</td>
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

		$Fieldset = new FormularFieldset( __('Marathon shape') );
		$Fieldset->addBlock( __('Your marathon shape is based on your weekly kilometers and your long jogs.<br>'.
								'The target is derived from the possible marathon time based on your current shape.').'<br>&nbsp;' );

		if (0 == Configuration::Data()->vdot()) {
			$Fieldset->addBlock('<p class="error">'.$this->getNoEffectiveVO2maxDataError().'</p><br>&nbsp;');
		}

		$Fieldset->addBlock($GeneralTable);
		$Fieldset->addBlock( __('The points for your long runs are weighted by time and quadratic in distance. '.
								'That means, a long jog yesterday gives more points than a long jog two weeks ago '.
								'and a 30k-jog gives more points than two 20k-jogs.').'<br>&nbsp;' );
		$Fieldset->addBlock($LongjogTable);
		$Fieldset->addBlock( __('The marathon shape is <strong>not</strong> scientifically based.<br>'.
								'It\'s our own attempt to adjust the prognosis for long distances based on your current endurance.') );

		return $Fieldset;
	}

	/**
	 * @return string
	 */
	public function getNoEffectiveVO2maxDataError() {
		return __('There are no current activities with an estimated VO<sub>2</sub>max value.').'<br>'.
			__('VO<sub>2</sub>max can only be estimated for runs with heart rate data.');
	}
}
