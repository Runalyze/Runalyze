<?php
/**
 * This file contains the class of the RunalyzePluginTool "Cacheclean".
 */
$PLUGINKEY = 'RunalyzePluginTool_Cacheclean';
/**
 * Class: RunalyzePluginTool_Cacheclean
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginTool
 * @uses class::Mysql
 * @uses class::Helper
 * @uses class::Draw
 */
class RunalyzePluginTool_Cacheclean extends PluginTool {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = 'Cacheclean';
		$this->description = 'L&ouml;scht den Cache der Diagramme. Sollte genutzt werden, falls Probleme mit Diagrammen auftauchen.';
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
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		echo 'Mit diesem Tool l&auml;sst sich der Cache der Diagramme l&ouml;schen.<br />'.NL;
		echo 'Es gehen dabei keine Daten verloren - es m&uuml;ssen lediglich alle Diagramme beim n&auml;chsten Aufruf neu berechnet werden.<br />'.NL;
		echo '<br />';

		if (!file_exists('draw/cache/cache.db') && !file_exists('draw/cache/index.db'))
			echo '<em>Es sind keine Cache-Dateien vorhanden.';
		elseif (isset($_GET['delete'])) {
			if (unlink('draw/cache/cache.db') && unlink('draw/cache/index.db'))
				echo '<em>Der Cache wurde erfolgreich gel&ouml;scht.</em>';
			else
				echo '<em>Der Cache konnte nicht gel&ouml;scht werden.</em>';
		} else
			echo self::getLink('<strong>Cache l&ouml;schen</strong>', 'delete=true');
	}
}
?>