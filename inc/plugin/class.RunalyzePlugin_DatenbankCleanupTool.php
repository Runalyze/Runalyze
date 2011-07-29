<?php
/**
 * This file contains the class of the RunalyzePlugin "DatenbankCleanupTool".
 */
$PLUGINKEY = 'RunalyzePlugin_DatenbankCleanupTool';
/**
 * Class: RunalyzePlugin_DatenbankCleanupTool
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginTool
 * @uses class::Mysql
 * @uses class::Helper
 * @uses class::Draw
 *
 * Last modified 2011/07/29 11:00 by Hannes Christiansen
 */
class RunalyzePlugin_DatenbankCleanupTool extends PluginTool {
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
		echo 'Dieser Vorgang betrifft lediglich einige zwischengespeicherte Werte wie die maximalen Werte f&uuml;r ATL/CTL/TRIMP.<br />'.NL;
		echo '<br />';

		if (isset($_GET['clean'])) {
			$this->cleanDatabase();

			echo '<em>Die Datenbank wurde erfolgreich bereinigt.</em>';
		} else
			echo self::getLink('<strong>Bereinigen</strong>', 'clean=true');
	}

	/**
	 * Clean the databse
	 */
	private function cleanDatabase() {
		$this->resetMaxValues();
		$this->resetShoes();

		// TODO: Optional
		// $this->resetTrimp();
		// $this->resetVdot();

		// TODO: Nicht existente Kleidung aus DB löschen
	}

	/**
	 * Clean the databse for max_atl, max_ctl, max_trimp
	 */
	private function resetMaxValues() {
		// Here ATL/CTL will be implemented again
		// Normal functions are too slow, calling them for each day would trigger each time a query
		// - ATL/CTL: SUM(`trimp`) for ATL_DAYS / CTL_DAYS
		$start_i = 365*START_YEAR;
		$end_i   = 365*(date("Y") + 1) - $start_i;
		$Trimp   = array_fill(0, $end_i, 0);
		$Data    = Mysql::getInstance()->fetchAsArray('
			SELECT
				YEAR(FROM_UNIXTIME(`time`)) as `y`,
				DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
				SUM(`trimp`) as `trimp`
			FROM `'.PREFIX.'training`
			GROUP BY `y`, `d`
			ORDER BY `y` ASC, `d` ASC');

		if (empty($Data))
			return;

		$maxATL   = 0;
		$maxCTL   = 0;

		foreach ($Data as $dat) {
			$atl = 0;
			$ctl = 0;

			$i = $dat['y']*365 + $dat['d'] - $start_i;
			$Trimp[$i] = $dat['trimp'];

			if ($i >= ATL_DAYS)
				$atl   = array_sum(array_slice($Trimp, $i - ATL_DAYS, ATL_DAYS)) / ATL_DAYS;
			if ($i >= CTL_DAYS)
				$ctl   = array_sum(array_slice($Trimp, $i - CTL_DAYS, CTL_DAYS)) / CTL_DAYS;

			if ($atl > $maxATL)
				$maxATL = $atl;
			if ($ctl > $maxCTL)
				$maxCTL = $ctl;
		}

		$maxTRIMP = max($Trimp);

		Mysql::getInstance()->query('UPDATE `'.PREFIX.'config` SET `max_atl`="'.$maxATL.'"');
		Mysql::getInstance()->query('UPDATE `'.PREFIX.'config` SET `max_ctl`="'.$maxCTL.'"');
		Mysql::getInstance()->query('UPDATE `'.PREFIX.'config` SET `max_trimp`="'.$maxTRIMP.'"');
	}

	/**
	 * Clean the databse for shoes
	 */
	private function resetShoes() {
		$shoes = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'schuhe`');
		foreach ($shoes as $shoe) {
			$data = Mysql::getInstance()->fetchSingle('SELECT SUM(`distanz`) as `km`, SUM(`dauer`) as `s` FROM `'.PREFIX.'training` WHERE `schuhid`="'.$shoe['id'].'" GROUP BY `schuhid`');
			Mysql::getInstance()->update(PREFIX.'schuhe', $shoe['id'], array('km', 'dauer'), array($data['km'], $data['s']));
		}
	}
}
?>