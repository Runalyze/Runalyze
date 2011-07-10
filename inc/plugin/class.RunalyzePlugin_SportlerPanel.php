<?php
/**
 * This file contains the class of the RunalyzePlugin "SportlerPanel".
 */
$PLUGINKEY = 'RunalyzePlugin_SportlerPanel';
/**
 * Class: RunalyzePlugin_SportlerPanel
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginPanel
 * @uses class::Mysql
 * @uses class::Ajax
 * @uses inc/draw/plugin.sportler.fett.php
 * @uses inc/draw/plugin.sportler.gewicht.php
 *
 * Last modified 2011/07/10 16:00 by Hannes Christiansen
 */
class RunalyzePlugin_SportlerPanel extends PluginPanel {
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

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		return Ajax::window('<a href="inc/plugin/window.sportler.php" title="Daten hinzuf&uuml;gen">'.Icon::get(Icon::$ADD, 'Daten hinzuf&uuml;gen').'</a>');
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		echo('
			<div id="sportler">
				<div id="sportler-gewicht" class="change">');

		$dat = Mysql::getInstance()->fetch('ltb_user', 'LAST');
		if (CONFIG_USE_GEWICHT == 1)
			$left = '<strong title="'.date("d.m.Y",$dat['time']).'">'.$dat['gewicht'].' kg</strong>';
		
		if (CONFIG_USE_RUHEPULS == 1)
			$right = $dat['puls_ruhe'].' bpm / '.$dat['puls_max'].' bpm';
		
		echo('		<p>
						<span>'.$right.'</span>
						<a class="change" href="sportler-analyse" target="sportler"><del>Analyse</del>/Allgemein:</a>
						'.$left.'
					</p>
	
					<div class="c">
						<img src="inc/draw/plugin.sportler.gewicht.php" alt="Diagramm" style="width:320px; height:148px;" />
					</div> 
				</div>
			<div id="sportler-analyse" class="change" style="display:none;">');

		$left = ''; $right = '';
		if (CONFIG_USE_KOERPERFETT == 1)
			$left = '<small>'.$dat['fett'].'&#37;Fett, '.$dat['wasser'].'&#37;Wasser, '.$dat['muskeln'].'&#37;Muskeln</small>';
	
		if (CONFIG_USE_BLUTDRUCK == 1) 
			$right = '<small>Blutdruck: '.$dat['blutdruck_min'].' zu '.$dat['blutdruck_max'];
	
		echo('		<p>
						<span>'.$right.'</span>
						<a class="change" href="sportler-gewicht" target="sportler">Analyse/<del>Allgemein</del>:</a>
						'.$lefl.'
					</p>
	
					<div class="c">
						<img src="inc/draw/plugin.sportler.fett.php" alt="Diagramm" style="width:320px; height:148px;" />
					</div> 
				</div>
			</div>');
	}
}
?>