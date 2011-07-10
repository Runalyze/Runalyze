<?php
/**
 * This file contains the class of the RunalyzePlugin "PrognosePanel".
 */
$PLUGINKEY = 'RunalyzePlugin_PrognosePanel';
/**
 * Class: RunalyzePlugin_PrognosePanel
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginPanel
 * @uses class::Helper
 *
 * Last modified 2011/07/10 16:00 by Hannes Christiansen
 */
class RunalyzePlugin_PrognosePanel extends PluginPanel {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Prognose';
		$this->description = 'Anzeige der aktuellen Wettkampfprognose.';
		
		Error::getInstance()->addTodo('PrognosePanel: Add Zwischenzeiten/Marschtabelle', __FILE__, __LINE__);
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
			echo Helper::Prognosis((double)$km, ((double)$km <= 3));
	}
}
?>