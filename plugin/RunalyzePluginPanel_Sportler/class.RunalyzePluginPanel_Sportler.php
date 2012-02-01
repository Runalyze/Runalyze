<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Sportler".
 */
$PLUGINKEY = 'RunalyzePluginPanel_Sportler';
/**
 * Class: RunalyzePluginPanel_Sportler
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginPanel
 * @uses class::Mysql
 * @uses class::Ajax
 * @uses inc/draw/plugin.sportler.fett.php
 * @uses inc/draw/plugin.sportler.gewicht.php
 */
class RunalyzePluginPanel_Sportler extends PluginPanel {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Sportler';
		$this->description = 'Anzeige der Sportlerdaten wie Gewicht und aktueller Ruhepuls (auch als Diagramm).';
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['use_weight']    = array('type' => 'bool', 'var' => true, 'description' => 'Gewicht protokollieren');
		$config['use_body_fat']  = array('type' => 'bool', 'var' => true, 'description' => 'Fettanteil protokollieren');
		$config['use_pulse']     = array('type' => 'bool', 'var' => true, 'description' => 'Ruhepuls protokollieren');
		$config['wunschgewicht'] = array('type' => 'int', 'var' => 0, 'description' => 'Wunschgewicht');

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = array();
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.sportler.php" title="Daten hinzuf&uuml;gen">'.Icon::get(Icon::$ADD, 'Daten hinzuf&uuml;gen').'</a>');
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.sportler.table.php" title="Daten in Tabelle anzeigen">'.Icon::get(Icon::$TABLE, 'Daten in Tabelle anzeigen').'</a>');

		return implode(' ', $Links);
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$Weight  = '';
		$Pulse   = '';
		$Analyse = '';
		$dat = User::getLastRow();

		if ($this->config['use_weight'])
			$Weight = 'Gewicht: <strong title="'.date("d.m.Y", $dat['time']).'">'.Helper::Unknown($dat['weight']).' kg</strong><br />';
		
		if ($this->config['use_pulse'])
			$Pulse = Helper::Unknown($dat['pulse_rest']).' bpm / '.Helper::Unknown($dat['pulse_max']).' bpm';

		if ($this->config['use_body_fat'])
			$Analyse = 'Fett: '.Helper::Unknown($dat['fat']).' &#37;, Wasser: '.Helper::Unknown($dat['water']).' &#37;, Muskeln: '.Helper::Unknown($dat['muscles']).' &#37;';
		
		echo('
			<div id="sportler">
				<span class="right">'.$Pulse.'</span>
				'.Ajax::flotChange($Weight, 'sportler_flots', 'sportler_weights').'
				'.Ajax::flotChange($Analyse, 'sportler_flots', 'sportler_analyse').'

				<div id="sportler_flots" class="flotChangeable" style="position:relative;width:322px;height:150px;margin:2px auto;">
					<div class="flot waitImg" id="sportler_weights" style="width:320px;height:148px;position:absolute;"></div>
					<div class="flot waitImg flotHide" id="sportler_analyse" style="width:320px;height:148px;position:absolute;"></div>
				</div>
			</div>');

		include FRONTEND_PATH.'../plugin/'.$this->key.'/Plot.gewicht.php';
		include FRONTEND_PATH.'../plugin/'.$this->key.'/Plot.analyse.php';
	}
}
?>