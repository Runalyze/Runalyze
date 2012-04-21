<?php
/**
 * This file contains the class of the RunalyzePluginTool "DatenbankCleanup".
 */
$PLUGINKEY = 'RunalyzePluginTool_DatenbankCleanup';
/**
 * Class: RunalyzePluginTool_DatenbankCleanup
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class RunalyzePluginTool_DatenbankCleanup extends PluginTool {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = 'Datenbank-Cleanup';
		$this->description = 'Reinigt die Datenbank. Dies ist unter Umst&auml;nden nach dem L&ouml;schen von Trainings notwendig.';
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
		echo 'Mit diesem Tool l&auml;sst sich die Datenbank bereinigen.<br />'.NL;
		echo 'Dieser Vorgang betrifft lediglich die summierten Daten der Schuhe und einige zwischengespeicherte Werte wie die maximalen Werte f&uuml;r ATL/CTL/TRIMP.<br />'.NL;
		echo '<br />';

		if (isset($_GET['clean'])) {
			$this->cleanDatabase();

			echo '<em>Die Datenbank wurde erfolgreich bereinigt.</em>';
		} else {
			echo '<ul>';
			echo '<li>'.self::getLink('<strong>Bereinigen</strong> (einfach)', 'clean=true').'</li>'.NL;
			echo '<li>'.self::getLink('<strong>Bereinigen</strong> (vollst&auml;ndig)*', 'clean=complete').'</li>'.NL;
			echo '</ul>'.NL;
			echo '<small>* Dann werden zun&auml;chst f&uuml;r alle Trainings TRIMP und VDOT neu berechnet.</small>';
		}
	}

	/**
	 * Clean the databse
	 */
	private function cleanDatabase() {
		if ($_GET['clean'] == 'complete')
			$this->resetTrimpAndVdot();

		$this->resetMaxValues();
		$this->resetShoes();

		// TODO: Nicht existente Kleidung aus DB loeschen
	}

	/**
	 * Reset all TRIMP- and VDOT-values in database
	 */
	private function resetTrimpAndVdot() {
		$Mysql = Mysql::getInstance();
		$IDs   = $Mysql->fetchAsArray('SELECT `id` FROM `'.PREFIX.'training`');

		foreach ($IDs as $ID)
			$Mysql->update(PREFIX.'training', $ID['id'],
				array('trimp', 'vdot'),
				array(Trimp::TRIMPfor($ID['id']), JD::Training2VDOT($ID['id'])));
	}

	/**
	 * Clean the databse for max_atl, max_ctl, max_trimp
	 */
	private function resetMaxValues() {
		Trimp::calculateMaxValues();
	}

	/**
	 * Clean the databse for shoes
	 */
	private function resetShoes() {
		$shoes = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'shoe`');
		foreach ($shoes as $shoe) {
			$data = Mysql::getInstance()->fetchSingle('SELECT SUM(`distance`) as `km`, SUM(`s`) as `s` FROM `'.PREFIX.'training` WHERE `shoeid`="'.$shoe['id'].'" GROUP BY `shoeid`');

			if ($data === false)
				$data = array('km' => 0, 's' => 0);

			Mysql::getInstance()->update(PREFIX.'shoe', $shoe['id'], array('km', 'time'), array($data['km'], $data['s']));
		}
	}
}