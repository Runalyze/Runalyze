<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Prognose".
 */
$PLUGINKEY = 'RunalyzePluginPanel_Prognose';
/**
 * Class: RunalyzePluginPanel_Prognose
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginPanel
 * @uses class::Helper
 */
class RunalyzePluginPanel_Prognose extends PluginPanel {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Prognose';
		$this->description = 'Anzeige der aktuellen Wettkampfprognose.';
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['distances']  = array('type' => 'array', 'var' => array(1, 3, 5, 10, 21.1, 42.2), 'description' => 'Distanzen f&uuml;r die Prognose (kommagetrennt)');

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		return '';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		foreach ($this->config['distances']['var'] as $km)
			$this->showPrognosis($km);

		if ($this->thereAreNotEnoughCompetitions())
			echo HTML::info('F&uuml;r gute Prognosen sind nicht genug Wettk&auml;mpfe da.');
	}

	/**
	 * Show prognosis for a given distance
	 * @param double $distance
	 */
	protected function showPrognosis($distance) {
		$Prognosis             = Helper::PrognosisAsArray($distance);
		$PersonalBestInSeconds = Helper::PersonalBest($distance, true);
		$PrognosisInSeconds    = $Prognosis['seconds'];
		$VDOTold               = round(JD::Competition2VDOT($distance, $PersonalBestInSeconds), 2);
		$VDOTnew               = round($Prognosis['vdot'], 2);

		if ($PersonalBestInSeconds > 0 && $PersonalBestInSeconds < $PrognosisInSeconds) {
			$oldTag = 'strong';
			$newTag = 'del';
		} else {
			$oldTag = 'del';
			$newTag = 'strong';
		}

		$oldTimeString  = Helper::Time($PersonalBestInSeconds);
		$newTimeString  = Helper::Time($PrognosisInSeconds);
		$paceString     = Helper::Pace($distance, $PrognosisInSeconds);
		$distanceString = Helper::Km($distance, 0, ($distance <= 3));

		echo '
			<p>
				<span class="right">
					<small>von</small> '.Ajax::tooltip('<'.$oldTag.'>'.$oldTimeString.'</'.$oldTag.'>', 'VDOT: '.$VDOTold).'
					<small>auf</small> '.Ajax::tooltip('<'.$newTag.'>'.$newTimeString.'</'.$newTag.'>', 'VDOT: '.$VDOTnew).'
					<small>('.$paceString.'/km)</small>
				</span>
				<strong>'.$distanceString.'</strong>
			</p>'.NL;
	}

	/**
	 * Are there not enough competitions?
	 * @return bool
	 */
	protected function thereAreNotEnoughCompetitions() {
		return 1 >= Mysql::getInstance()->num('SELECT 1 FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID);
	}
}
?>