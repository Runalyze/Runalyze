<?php
/**
 * This file contains the class of the RunalyzePluginTool "Cacheclean".
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_Cacheclean';
/**
 * Class: RunalyzePluginTool_Cacheclean
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzePluginTool_Cacheclean extends PluginTool {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = 'Cacheclean';
		$this->description = 'L&ouml;scht den Trainings-Cache.';
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
		if (isset($_GET['delete']))
			System::clearCache();

		$numData = Mysql::getInstance()->fetchSingle('SELECT COUNT(*) as num FROM '.PREFIX.'training WHERE gps_cache_object!="" LIMIT 1');
		$num     = $numData['num'];

		$Fieldset = new FormularFieldset('Cache l&ouml;schen');
		$Fieldset->addInfo(self::getActionLink('<strong>Cache l&ouml;schen</strong>', 'delete=true').'<br />
			Zur schnellen Trainingsanzeige werden die berechneten GPS-Daten (Runden, Zonen, Diagramme und Streckenverlauf)
			im Cache gespeichert. Falls Probleme dabei auftauchen, kann &uuml;ber dieses Plugin der Cache geleert werden.');
		$Fieldset->addFileBlock('Insgesamt sind '.$num.' Trainings im Cache.');

		$Formular = new Formular();
		$Formular->setId('cacheclean-form');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}
}
?>