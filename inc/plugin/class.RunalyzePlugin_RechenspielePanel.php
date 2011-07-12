<?php
/**
 * This file contains the class of the RunalyzePlugin "RechenspielePanel".
 */
$PLUGINKEY = 'RunalyzePlugin_RechenspielePanel';
/**
 * Class: RunalyzePlugin_RechenspielePanel
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginPanel
 * @uses class::Helper
 * @uses class::JD
 * @uses class::Ajax
 * @uses CONFIG_MAX_ATL
 * @uses CONFIG_MAX_CTL
 * @uses VDOT_FORM
 *
 * Last modified 2011/07/10 16:00 by Hannes Christiansen
 */
class RunalyzePlugin_RechenspielePanel extends PluginPanel {
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
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		return Ajax::window('<a href="inc/plugin/window.rechenspiele.form.php" title="Form anzeigen">'.Icon::get(Icon::$FATIGUE, 'Form anzeigen').'</a>').NL;
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		echo('<small class="right r">');

		$vVDOT = JD::VDOT2v(VDOT_FORM);

		$t = array();
		$t[] = array('kurz' => 'RL', 'pVDOT' => '59-64');
		$t[] = array('kurz' => 'DL', 'pVDOT' => '65-74');
		$t[] = array('kurz' => 'LL', 'pVDOT' => '65-74');
		$t[] = array('kurz' => 'TDL', 'pVDOT' => '83-88');
		$t[] = array('kurz' => 'IT', 'pVDOT' => '95-100');
		$t[] = array('kurz' => 'WHL', 'pVDOT' => '105-110');

		foreach ($t as $train) {
			$train_tempo = explode('-',$train['pVDOT']);
			echo ('
				'.$train['kurz'].': <em>'.JD::v2Pace($vVDOT*$train_tempo[1]/100).'</em> - <em>'.JD::v2Pace($vVDOT*$train_tempo[0]/100).'</em>/km<br />');
		}

		echo('</small>
			<div class="left" style="width:60%;">
				<p><span>'.round(100*Helper::ATL()/CONFIG_MAX_ATL).' &#37;</span> <strong>M&uuml;digkeit</strong> <small>(ATL)</small></p>
				<p><span>'.round(100*Helper::CTL()/CONFIG_MAX_CTL).' &#37;</span> <strong>Fitnessgrad</strong> <small>(CTL)</small></p>
				<p><span>'.Helper::TSB().'</span> <strong>Stress Balance</strong> <small>(TSB)</small></p>
				<p><span>'.round(VDOT_FORM,2).'</span> <strong>VDOT</strong></p>
				<p><span>'.Helper::BasicEndurance().'</span> <strong>Grundlagenausdauer</strong></p>
			</div>');

		echo Helper::clearBreak();

		// Fix for clear break in IE
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
			echo '&nbsp;';
	}
}
?>