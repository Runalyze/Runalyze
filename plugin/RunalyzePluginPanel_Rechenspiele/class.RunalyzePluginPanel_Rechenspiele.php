<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Rechenspiele".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Rechenspiele';
/**
 * Class: RunalyzePluginPanel_Rechenspiele
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Rechenspiele extends PluginPanel {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Rechenspiele';
		$this->description = 'Anzeige von Rechenspielen zur M&uuml;digkeit, Grundlagenausdauer und Trainingsform. Zus&auml;tzlich werden auch empfohlene Trainingsgeschwindigkeiten angezeigt.';
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('Bei Runalyze werden viele Tabellen und daraus abgeleitete Formeln von &quot;Jack Daniels - Die Laufformel&quot; verwendet.
				Unter anderem wird aus dem Verh&auml;ltnis von Herzfrequenz und Tempo auf die aktuelle Form geschlossen.');
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['show_trainingpaces']  = array('type' => 'bool', 'var' => true, 'description' => Ajax::tooltip('Anzeige: Trainingstempo', 'Empfohlene Trainingspaces anzeigen', true));
		$config['show_trimpvalues']    = array('type' => 'bool', 'var' => true, 'description' => Ajax::tooltip('Anzeige: ATL/CTL/TSB', 'Statistische Werte M&uuml;digkeit, Fitnessgrad und Stress Balance anzeigen', true));
		$config['show_vdot']           = array('type' => 'bool', 'var' => true, 'description' => Ajax::tooltip('Anzeige: VDOT', 'Aktuellen berechneten VDOT anzeigen', true));
		$config['show_basicendurance'] = array('type' => 'bool', 'var' => true, 'description' => Ajax::tooltip('Anzeige: Grundlagenausdauer', 'Prozentwert f&uuml;r die Grundlagenausdauer anzeigen', true));

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = array();
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.plot.php" '.Ajax::tooltip('', 'Form anzeigen', true, true).'>'.Icon::$FATIGUE.'</a>');
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.php" '.Ajax::tooltip('', 'Berechnungen der Werte', true, true).'>'.Icon::$CALCULATOR.'</a>');
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.info.html" '.Ajax::tooltip('', 'Erl&auml;uterungen zu den Rechenspielen', true, true).'>'.Icon::$INFO.'</a>');

		return implode(NBSP, $Links);
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		if ($this->config['show_trainingpaces']['var']) {
			echo '<small class="right r '.(VDOT_FORM==0?'unimportant':'').'">';
			$this->showPaces();
			echo '</small>';

			echo '<div class="left" style="width:60%;">';
			$this->showValues();
			echo '</div>';

			echo HTML::clearBreak();

			if (HTML::isInternetExplorer())
				echo '&nbsp;';
		} else {
			$this->showValues();
		}

		if (Time::diffInDays(START_TIME) < 70)
			echo HTML::info('F&uuml;r sinnvolle Werte sind zu wenig Daten da.');
	}

	/**
	 * Show values
	 */
	protected function showValues() {
		$TrimpValues = Trimp::arrayForATLandCTLandTSBinPercent();

		$Values = array(
			array(
				'show'	=> $this->config['show_trimpvalues']['var'],
				'value'	=> $TrimpValues['ATL'].' &#37;',
				'title'	=> 'M&uuml;digkeit',
				'small'	=> '(ATL)',
				'tooltip'	=> 'Actual Training Load<br /><small>Durchschnittliche Trainingsbelastung der letzten Woche verglichen mit dem bisherigen Maximalwert.</small>'
			),
			array(
				'show'	=> $this->config['show_trimpvalues']['var'],
				'value'	=> $TrimpValues['CTL'].' &#37;',
				'title'	=> 'Fitnessgrad',
				'small'	=> '(CTL)',
				'tooltip'	=> 'Chronical Training Load<br /><small>Durchschnittliche Trainingsbelastung des letzten Monats verglichen mit dem bisherigen Maximalwert.</small>'
			),
			array(
				'show'	=> $this->config['show_trimpvalues']['var'],
				'value'	=> $TrimpValues['TSB'],
				'title'	=> 'Stress Balance',
				'small'	=> '(TSB)',
				'tooltip'	=> 'Training Stress Balance (= CTL - ATL)<br />Positiver Wert: Du bist erholt.<br />
					Negativer Wert: Du trainierst hart.<br />
					<small>Ein Wert von +10 oder h&ouml;her ist f&uuml;r einen Wettkampf zu empfehlen.<br />
					Bei Werten unter -10 solltest du sicher sein, dass dein K&ouml;rper das vertr&auml;gt.</small>'
			),
			array(
				'show'	=> $this->config['show_vdot']['var'],
				'value'	=> round(VDOT_FORM,2),
				'title'	=> 'VDOT',
				'small'	=> '',
				'tooltip'	=> 'Aktueller durchschnittlicher VDOT-Wert'
			),
			array(
				'show'	=> $this->config['show_basicendurance']['var'],
				'value'	=> BasicEndurance::getConst().' &#37;',
				'title'	=> 'Grundlagenausdauer',
				'small'	=> '',
				'tooltip'	=> '<em>Experimenteller Wert!</em><br />100 &#37; entspricht dem Optimum an Wochenkilometern und Langen L&auml;ufen f&uuml;r einen perfekten Marathon bei deinem derzeitigen VDOT.'
			)
		);

		foreach ($Values as $Value) {
			if ($Value['show']) {
				$Label = '<strong>'.$Value['title'].'</strong> <small>'.$Value['small'].'</small>';
				$Text = $Value['tooltip'] != '' ? Ajax::tooltip($Label, $Value['tooltip']) : $Label;
				echo '<p><span class="right">'.$Value['value'].'</span> '.$Text.'</p>';
			}
		}
	}

	/**
	 * Show paces
	 */
	protected function showPaces() {
		$Paces = $this->getArrayForPaces();
		$vVDOT = JD::VDOT2v(VDOT_FORM);

		foreach ($Paces as $Pace)
			echo ($Pace['short'].': <em>'.JD::v2Pace($vVDOT*$Pace['limit-low']/100).'</em> - <em>'.JD::v2Pace($vVDOT*$Pace['limit-high']/100).'</em>/km<br />');
	}

	/**
	 * Get array for paces
	 * @return array 
	 */
	protected function getArrayForPaces() {
		$Paces = array(
			array(
				'short'			=> 'RL',
				'name'			=> 'Regenerationslauf',
				'description'	=> 'Dieses Tempo kommt bei Jack Daniels eigentlich gar nicht vor.',
				'limit-low'		=> 59,
				'limit-high'	=> 64
			),
			array(
				'short'			=> 'DL',
				'name'			=> 'Dauerlauf',
				'description'	=> 'Zum Training der Grundlagenausdauer. Hier kommt es nicht auf Sekunden an.',
				'limit-low'		=> 65,
				'limit-high'	=> 74
			),
			array(
				'short'			=> 'LL',
				'name'			=> 'Langer Lauf',
				'description'	=> 'Der LL wird bei Jack Daniels im gleichen Tempo wie ein normaler DL gelaufen.',
				'limit-low'		=> 65,
				'limit-high'	=> 74
			),
			array(
				'short'			=> 'TDL',
				'name'			=> 'Tempodauerlauf',
				'description'	=> 'Schwellentempo an der anaeroben Schwelle, um diese anzuheben.',
				'limit-low'		=> 83,
				'limit-high'	=> 88
			),
			array(
				'short'			=> 'IT',
				'name'			=> 'Intervalltraining',
				'description'	=> 'Zum Training der maximalen Sauerstoffaufnahme.',
				'limit-low'		=> 95,
				'limit-high'	=> 100
			),
			array(
				'short'			=> 'WHL',
				'name'			=> 'Wiederholungsl&auml;ufe',
				'description'	=> 'W&auml;hrend beim IT die Pausen k&uuml;rzer als die schnellen Abschnitte sind, erfolgt beim WHL in der Pause eine vollst&auml;dige Erholung.',
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
		$TrimpValues = Trimp::arrayForATLandCTLandTSBinPercent();
		$ATL         = Trimp::ATL();
		$CTL         = Trimp::CTL();
		$maxATL      = Trimp::maxATL();
		$maxCTL      = Trimp::maxCTL();

		$Table = '
			<table class="fullwidth zebra-style">
				<thead>
					<tr>
						<th></th>
						<th>Name</th>
						<th>in &#37;</th>
						<th>Zeitraum</th>
						<th>&oslash; Wert/Tag</th>
						<th>max. Wert</th>
						<th class="small">Beschreibung</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="b">ATL</td>
						<td>Actual Training Load</td>
						<td class="c">'.$TrimpValues['ATL'].' &#37;</td>
						<td class="c small">'.CONF_ATL_DAYS.' Tage</td>
						<td class="c">'.$ATL.'</td>
						<td class="c">'.$maxATL.'</td>
						<td class="small">Belastung &uuml;ber einen kurzen Zeitraum.</td>
					</tr>
					<tr>
						<td class="b">CTL</td>
						<td>Chronical Training Load</td>
						<td class="c">'.$TrimpValues['CTL'].' &#37;</td>
						<td class="c small">'.CONF_CTL_DAYS.' Tage</td>
						<td class="c">'.$CTL.'</td>
						<td class="c">'.$maxCTL.'</td>
						<td class="small">Belastung &uuml;ber einen l&auml;ngeren Zeitraum.</td>
					</tr>
					<tr>
						<td class="b">TSB</td>
						<td>Training Stress Balance</td>
						<td class="c">'.$TrimpValues['TSB'].'</td>
						<td colspan="3" class="c">'.$CTL.' - '.$ATL.' = '.$TrimpValues['TSB'].'</td>
						<td class="small">
							Aktuelle Belastung<br />
							positiv = Erholung,
							negativ = Anstrengung<br />
						</td>
					</tr>
				</tbody>
			</table>';

		$Fieldset = new FormularFieldset('ATL/CTL/TSB');
		$Fieldset->addBlock('ATL/CTL basieren auf dem TRIMP-Konzept, das jedem Training einen Belastungswert zuteilt.
							ATL und CTL sind Mittelwerte von TRIMP.
							Da die Werte selbst wenig Aussagekraft haben,
							werden sie bei Runalyze in Prozent vom bisherigen Maximum angegeben.');
		$Fieldset->addBlock('100&#37; entspricht also der maximalen sportlichen Belastung,
							die du im angegebenen Zeitraum dir bisher je zugemutet hast (soweit hier eingetragen).
							Ob du noch mehr Training verkraftest oder diese Belastung bereits zu viel ist,
							kann Runalyze dir nicht sagen.');
		$Fieldset->addBlock($Table);
		$Fieldset->addInfo('siehe <a href="http://www.netzathleten.de/Sportmagazin/Richtig-trainieren/Das-TRIMP-Konzept/1730751739988967389/head/page1" title="Das TRIMP-Konzept">Das TRIMP-Konzept</a> auf netzathleten.de');

		return $Fieldset;
	}

	/**
	 * Get fieldset for VDOT
	 * @return \FormularFieldset 
	 */
	public function getFieldsetVDOT() {
		$Table = '
			<table class="fullwidth zebra-style">
				<thead>
					<tr>
						<th colspan="10">VDOT-Werte der letzten '.CONF_VDOT_DAYS.' Tage</th>
					</tr>
				</thead>
				<tbody class="top-and-bottom-border">
				';

		$VDOTs = Mysql::getInstance()->fetchAsArray('SELECT `id`,`time`,`distance`,`vdot` FROM `'.PREFIX.'training` WHERE time>='.(time() - CONF_VDOT_DAYS*DAY_IN_S).' AND vdot>0 AND use_vdot=1 ORDER BY time ASC');
		foreach ($VDOTs as $i => $Data) {
			if ($i%10 == 0)
				$Table .= '<tr>'.NL;

			$Link   = Ajax::trainingLink($Data['id'], round(JD::correctVDOT($Data['vdot']), 2));
			$Title  = Running::Km($Data['distance']).' am '.date('d.m.Y', $Data['time']);
			$Table .= '<td>'.Ajax::tooltip($Link, $Title).'</td>'.NL;

			if ($i%10 == 9)
				$Table .= '</tr>'.NL;
		}

		if (count($VDOTs)%10 != 0)
			$Table .= HTML::emptyTD(10 - count($VDOTs)%10);

		$Table .= '
				</tbody>
			</table>
			';

		$Fieldset = new FormularFieldset('VDOT');
		$Fieldset->addBlock('Die VDOT-Form berechnet sich aus dem Mittelwert der VDOT-Werte deiner
							Trainingseinheiten der letzten '.CONF_VDOT_DAYS.' Tage. Die Werte werden nach der jeweiligen Dauer gewichtet.');
		$Fieldset->addBlock('Dein aktuelle VDOT-Form: <strong>'.VDOT_FORM.'</strong><br />&nbsp;');
		$Fieldset->addBlock($Table);
		$Fieldset->addInfo('Bei Jack Daniels wird der VDOT als fester Wert angesehen und nicht aus Trainingsleistungen berechnet.<br />
							Die hier verwendeten Berechnung anhand der Pulsdaten wurden lediglich aus seinen Puls-Tabellen abgeleitet.');

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
						<td><strong>Aktueller VDOT</strong> <small>(nach Puls)</small></td>
						<td class="r">'.round(VDOT_FORM, 2).'</td>
						<td>&nbsp;</td>
						<td><strong>Vorgabe Wochen-KM</strong> <small>('.round($BasicEndurance->getDaysForWeekKm() / 7).' Wochen)</small></td>
						<td class="r">'.Running::Km($BasicEndurance->getTargetWeekKm()).'</td>
						<td class="small">erreicht zu '.round(100*$BEresults['weekkm-percentage']).'&#37;</td>
						<td class="small">(&oslash; '.Running::Km(($BEresults['weekkm-result'] / $BasicEndurance->getDaysForWeekKm() * 7), 0).')</td>
						<td class="small">x'.$BasicEndurance->getPercentageForWeekKilometer().'</td>
						<td rowspan="2" class="bottom-spacer b" style="vertical-align:middle;">= '.round($BEresults['percentage']).'&#37;</td>
					</tr>
					<tr>
						<td><strong>Marathonzeit</strong> <small>(optimal)</small></td>
						<td class="r">'.Time::toString($Prognosis->inSeconds(42.195)).'</td>
						<td>&nbsp;</td>
						<td><strong>Vorgabe Langer Lauf</strong> <small>('.round($BasicEndurance->getDaysToRecognizeForLongjogs() / 7).' Wochen)</small></td>
						<td class="r">'.Running::Km($BasicEndurance->getRealTargetLongjogKmPerWeek()).'</td>
						<td class="small">erreicht zu '.round(100*$BEresults['longjog-percentage']).'&#37;</td>
						<td class="small">('.round($BEresults['longjog-result'], 1).' points)</td>
						<td class="small">x'.$BasicEndurance->getPercentageForLongjogs().'</td>
					</tr>
				</tbody>
			</table>';

		$LongjogTable = '
			<table class="fullwidth zebra-style c">
				<thead>
					<tr>
						<th>Datum*</th>
						<th>Distanz</th>
						<th>Punkte</th>
					</tr>
				</thead>
				<tbody>
			';

		$IgnoredLongjogs = 0;
		$Longjogs        = Mysql::getInstance()->fetchAsArray($BasicEndurance->getQuery(0, true));

		foreach ($Longjogs as $Longjog) {
			if ($Longjog['points'] >= 0.2)
				$LongjogTable .= '
						<tr>
							<td>'.Ajax::trainingLink($Longjog['id'], date('d.m.Y', $Longjog['time'])).'</td>
							<td>'.Running::Km($Longjog['distance']).'</td>
							<td>'.round($Longjog['points'], 1).' points</td>
						</tr>';
			else
				$IgnoredLongjogs++;
		}

		$LongjogTable .= '
					<tr class="top-spacer no-zebra">
						<td></td>
						<td></td>
						<td class="b">= '.round($BEresults['longjog-result'], 1).' points</td>
					</tr>
				</tbody>
			</table>';
		$LongjogTable .= '<p class="small">* '.$IgnoredLongjogs.' &quot;lange&quot; L&auml;ufe wurden hier nicht aufgef&uuml;hrt, da sie weniger als 0.2 Punkte eingebracht haben.</p>';
		$LongjogTable .= '<p class="small">* Generell werden alle L&auml;ufe ab '.Running::Km($BasicEndurance->getMinimalDistanceForLongjogs()).' betrachtet.</p>';

		$Fieldset = new FormularFieldset('Grundlagenausdauer');
		$Fieldset->addBlock('Die Grundlagenausdauer berechnet sich aus Wochenkilometern und langen L&auml;ufen.<br />
							Die Vorgaben daf&uuml;r richten sich nach deinem VDOT-Wert und der (daraus) angestrebten Marathon-Zeit.');
		$Fieldset->addBlock($GeneralTable);
		$Fieldset->addBlock('Die Punkte f&uuml;r die Langen L&auml;ufe werden in zeitlicher Abh&auml;ngigkeit
							und quadratisch gr&ouml;&szlig;er werdend vergeben.<br />
							Ein Langer Lauf gestern bringt mehr als ein Langer Lauf vor einigen Wochen
							und ein 30 km-Lauf bringt mehr als zwei 20 km-L&auml;ufe.');
		$Fieldset->addBlock($LongjogTable);
		$Fieldset->addInfo('Die Grundlagenausdauer stammt <strong>nicht</strong> von Jack Daniels.<br />
							Um die Prognosen auf langen Distanzen bei fehlender Ausdauer anzupassen,
							haben wir diesen Algorithmus entworfen. Er ist allerdings durchaus
							diskussionsw&uuml;rdig.');

		return $Fieldset;
	}

	/**
	 * Get fieldset for paces
	 * @return \FormularFieldset 
	 */
	public function getFieldsetPaces() {
		$Table = '
			<table class="fullwidth zebra-style">
				<thead>
					<tr>
						<th></th>
						<th>Name</th>
						<th class="small">Pace</th>
						<th class="small">Beschreibung</th>
					</tr>
				</thead>
				<tbody>
			';

		$vVDOT = JD::VDOT2v(VDOT_FORM);
		foreach ($this->getArrayForPaces() as $Pace) {
			$Table .= '
					<tr>
						<td class="b">'.$Pace['short'].'</td>
						<td>'.$Pace['name'].'</td>
						<td class="small"><em>'.JD::v2Pace($vVDOT*$Pace['limit-low']/100).'&nbsp;-&nbsp;'.JD::v2Pace($vVDOT*$Pace['limit-high']/100).'/km</em></td>
						<td class="small">'.$Pace['description'].'</td>
					</tr>';
		}

		$Table .= '
				</tbody>
			</table>';

		$Fieldset = new FormularFieldset('Trainingstempo');
		$Fieldset->addBlock($Table);
		$Fieldset->addInfo('Diese Vorgaben richten sich nach den Trainingstempos von Jack Daniels (E/T/I/R-pace).');

		return $Fieldset;
	}
}
?>