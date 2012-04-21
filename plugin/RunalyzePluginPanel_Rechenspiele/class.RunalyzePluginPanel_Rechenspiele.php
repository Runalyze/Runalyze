<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Rechenspiele".
 */
$PLUGINKEY = 'RunalyzePluginPanel_Rechenspiele';
/**
 * Class: RunalyzePluginPanel_Rechenspiele
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginPanel
 * @uses class::Helper
 * @uses class::JD
 * @uses class::Ajax
 * @uses MAX_ATL
 * @uses MAX_CTL
 * @uses VDOT_FORM
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
		echo HTML::p('Die Form wird als <strong>VDOT</strong> angegeben, einer rechnerischen Gr&ouml;&szlig;e f&uuml;r die maximale Sauerstoffaufnahme.
				Mittels dieser k&ouml;nnen ein Trainingstempo und eine m&ouml;gliche Wettkampfzeit berechnet werden.');
		echo HTML::p('Da die Prognosen f&uuml;r lange Distanzen eher zu gut sind, wird ein eigener Algorithmus zur
				Bestimmung der <strong>Grundlagenausdauer</strong> verwendet.
				Dieser Wert ist sehr experimentell und mit Vorsicht zu genie&szlig;en.');
		echo HTML::p('Fundierter sind die Grundlagen f&uuml;r ATL/CTL/TSB.
				Aus Dauer und Puls wird eine Trainingsbelastung bestimmt, auch <em>Training Load</em> genannt.');
		echo HTML::p('Die <strong>M&uuml;digkeit</strong> (<em>Actual Training Load</em>) steht dabei f&uuml;r die Belastung der letzten Woche,
				der <strong>Fitnessgrad</strong> (<em>Chronic Training Load</em>) f&uuml;r die langfristige Belastung.
				Zur Vergleichbarkeit wird jeweils der Prozentwert von der bisher maximalen Trainingsbelastung angegeben.');
		echo HTML::p('Das Verh&auml;ltnis der beiden ist die <strong>Training Stress Balance</strong>.
				Ein positiver Wert steht hierbei f&uuml;r Erholung, ein negativer f&uuml;r intensives Training.');
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['show_trainingpaces']  = array('type' => 'bool', 'var' => true, 'description' => '<abbr class="atLeft" tooltip="Empfehlung anzeigen">Trainingstempo</abbr>');

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = array();
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.php">'.Icon::get(Icon::$FATIGUE, '', '', 'Form anzeigen').'</a>');
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.info.html">'.Icon::get(Icon::$INFO, '', '', 'Erl&auml;uterungen zu den Rechenspielen').'</a>');

		return implode(' ', $Links);
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		if ($this->config['show_trainingpaces']['var']) {
			$t = array();
			$t[] = array('kurz' => 'RL', 'pVDOT' => '59-64');
			$t[] = array('kurz' => 'DL', 'pVDOT' => '65-74');
			$t[] = array('kurz' => 'LL', 'pVDOT' => '65-74');
			$t[] = array('kurz' => 'TDL', 'pVDOT' => '83-88');
			$t[] = array('kurz' => 'IT', 'pVDOT' => '95-100');
			$t[] = array('kurz' => 'WHL', 'pVDOT' => '105-110');

			$vVDOT = JD::VDOT2v(VDOT_FORM);
		
			echo '<small class="right r '.(VDOT_FORM==0?'unimportant':'').'">';

			foreach ($t as $train) {
				$train_tempo = explode('-',$train['pVDOT']);
				echo ('
					'.$train['kurz'].': <em>'.JD::v2Pace($vVDOT*$train_tempo[1]/100).'</em> - <em>'.JD::v2Pace($vVDOT*$train_tempo[0]/100).'</em>/km<br />');
			}

			echo '</small>';
		}

		echo('
			<div class="left" style="width:60%;">
				<p><span class="right">'.Trimp::ATLinPercent().' &#37;</span> <strong>M&uuml;digkeit</strong> <small>(ATL)</small></p>
				<p><span class="right">'.Trimp::CTLinPercent().' &#37;</span> <strong>Fitnessgrad</strong> <small>(CTL)</small></p>
				<p><span class="right">'.Trimp::TSB().'</span> <strong>Stress Balance</strong> <small>(TSB)</small></p>
				<p><span class="right">'.round(VDOT_FORM,2).'</span> <strong>VDOT</strong></p>
				<p><span class="right">'.Helper::BasicEndurance().'</span> <strong>Grundlagenausdauer</strong></p>
			</div>');

		echo HTML::clearBreak();

		if (HTML::isInternetExplorer())
			echo '&nbsp;';

		if (Time::diffInDays(START_TIME) < 70)
			echo HTML::info('F&uuml;r sinnvolle Werte sind zu wenig Daten da.');
	}
}
?>