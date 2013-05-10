<?php
/**
 * This file contains the class of the RunalyzePluginTool "DatenbankCleanup".
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_DatenbankCleanup';
/**
 * Class: RunalyzePluginTool_DatenbankCleanup
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzePluginTool_DatenbankCleanup extends PluginTool {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = 'Datenbank-Cleanup';
		$this->description = 'Reinigt die Datenbank. Dies ist unter Umst&auml;nden nach dem L&ouml;schen von Trainings notwendig.<br />
			Au&szlig;erdem k&ouml;nnen die H&ouml;henmeter-, TRIMP- und VDOT-Werte neu berechnet werden.';
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('Um die Statistiken zu beschleunigen, werden einige Maximalwerte und Summen einzeln abgespeichert,
					anstatt sie immer neu zu berechnen. Das L&ouml;schen von Trainings kann dabei zu Problemen f&uuml;hren.');
		echo HTML::p('Wenn irgendwo bei den Statistiken Unstimmigkeiten auftreten, kann dieses Tool eventuell helfen.');
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
		if (isset($_GET['clean'])) {
			$this->cleanDatabase();
			echo '<em>Die Datenbank wurde erfolgreich bereinigt.</em><br /><br />';
		}

		$Fieldset = new FormularFieldset('Datenbank bereinigen');
		$Fieldset->addBlock('Mit diesem Tool l&auml;sst sich die Datenbank bereinigen.
			Dieser Vorgang betrifft lediglich die summierten Daten der Schuhe und
			einige zwischengespeicherte Werte wie die maximalen Werte f&uuml;r ATL/CTL/TRIMP.');
		$Fieldset->addBlock('&nbsp;');
		$Fieldset->addInfo('<strong>'.self::getActionLink('Einfache Bereinigung', 'clean=simple').'</strong><br />
			Hierbei werden die Statistiken der Schuhe und die maximalen Werte f&uuml;r ATL/CTL/TRIMP neu berechnet.');
		$Fieldset->addInfo('<strong>'.self::getActionLink('Vollst&auml;ndige Bereinigung', 'clean=complete').'</strong><br />
			Hierbei werden zun&auml;chst f&uuml;r alle Trainings die TRIMP- und VDOT-Werte neu berechnet und
			anschlie&szlig;end die Statistiken der Schuhe und die maximalen Werte f&uuml;r ATL/CTL/TRIMP neu berechnet.');
		$Fieldset->addInfo('<strong>'.self::getActionLink('H&ouml;henmeter neu berechnen', 'clean=elevation').'</strong><br />
			F&uuml;r alle Trainings mit GPS-Daten werden die H&ouml;henmeter neu berechnet.
			Dies ist notwendig, wenn die Konfigurationseinstellungen bez&uuml;glich der Berechnung ge&auml;ndert wurden.');

		$Formular = new Formular();
		$Formular->setId('datenbank-cleanup');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Clean the databse
	 */
	private function cleanDatabase() {
		if ($_GET['clean'] == 'complete')
			$this->resetTrimpAndVdot();

		if ($_GET['clean'] == 'simple' || $_GET['clean'] == 'complete') {
			$this->resetMaxValues();
			$this->resetShoes();
		}

		if ($_GET['clean'] == 'elevation')
			$this->calculateElevation();

		// TODO: Nicht existente Kleidung aus DB loeschen
	}

	/**
	 * Reset all TRIMP- and VDOT-values in database
	 */
	private function resetTrimpAndVdot() {
		$Mysql     = Mysql::getInstance();
		$Trainings = $Mysql->fetchAsArray('SELECT `id`,`sportid`,`typeid`,`distance`,`s`,`pulse_avg` FROM `'.PREFIX.'training`');

		foreach ($Trainings as $Training)
			$Mysql->update(PREFIX.'training', $Training['id'],
				array(
					'trimp',
					'vdot',
					'vdot_by_time'
				),
				array(
					Trimp::forTraining($Training),
					JD::Training2VDOT($Training['id'], $Training),
					JD::Competition2VDOT($Training['distance'], $Training['s'])
				));
	}

	/**
	 * Calculate elevation
	 */
	private function calculateElevation() {
		$Mysql     = Mysql::getInstance();
		$Trainings = $Mysql->fetchAsArray('SELECT `id`,`arr_alt`,`arr_time` FROM `'.PREFIX.'training` WHERE `arr_alt`!=""');

		foreach ($Trainings as $Training) {
			$GPS = new GpsData($Training);

			$Mysql->update(PREFIX.'training', $Training['id'], 'elevation', $GPS->calculateElevation() );
		}
	}

	/**
	 * Clean the databse for max_atl, max_ctl, max_trimp
	 */
	private function resetMaxValues() {
		Trimp::calculateMaxValues();
		JD::recalculateVDOTcorrector();
	}

	/**
	 * Clean the databse for shoes
	 */
	private function resetShoes() {
		ShoeFactory::recalculateAllShoes();
	}
}