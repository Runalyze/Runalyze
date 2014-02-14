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
	 * Success messages
	 * @var array
	 */
	protected $SuccessMessages = array();

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

			foreach ($this->SuccessMessages as $Message)
				echo HTML::okay($Message);
		}

		$AndApplyElevationToVDOT = CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION ? ' und VDOT anpassen' : '';

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
		$Fieldset->addInfo('<strong>'.self::getActionLink('H&ouml;henmeter neu berechnen'.$AndApplyElevationToVDOT, 'clean=elevation').'</strong><br />
			F&uuml;r alle Trainings mit GPS-Daten werden die H&ouml;henmeter neu berechnet.<br />
			Dies ist notwendig, wenn die Konfigurationseinstellungen bez&uuml;glich der Berechnung ge&auml;ndert wurden.<br />
			<br />
			<small>&Auml;ndert nur den berechneten Wert, der nur in der genauen Trainingsansicht auftaucht.</small>');
		$Fieldset->addInfo('<strong>'.self::getActionLink('H&ouml;henmeter neu berechnen'.$AndApplyElevationToVDOT.' (manuelle Eingabe &uuml;berschreiben)', 'clean=elevation&overwrite=true').'</strong><br />
			Die Anzeige bezieht sich auf die manuell eingegebenen H&ouml;henmeter, welche nur einen berechneten Wert enthalten, wenn das Feld im Formular leer gelassen wurde.
			Mit dieser Methode k&ouml;nnen diese Werte &uuml;berschrieben werden.');

		if (CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION) {
			$Fieldset->addWarning('Da die VDOT-Anpassung an H&ouml;henmeter aktiviert ist, m&uuml;ssen zum Neuberechnen der VDOT-Werte
				auch die H&ouml;henmeter neuberechnet werden. Die vollst&auml;ndige Bereinigung passt den VDOT daher nicht korrekt an.');
		}

		$Formular = new Formular();
		$Formular->setId('datenbank-cleanup');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Clean the databse
	 */
	private function cleanDatabase() {
		$this->SuccessMessages[] = 'Die Datenbank wurde erfolgreich bereinigt.';

		if ($_GET['clean'] == 'complete')
			$this->resetTrimpAndVdot();

		if ($_GET['clean'] == 'simple' || $_GET['clean'] == 'complete') {
			$this->resetMaxValues();
			$this->resetShoes();
		}

		if ($_GET['clean'] == 'elevation')
			$this->calculateElevation();

		JD::recalculateVDOTform();
		BasicEndurance::recalculateValue();
		Helper::recalculateStartTime();
		Helper::recalculateHFmaxAndHFrest();

		// TODO: Nicht existente Kleidung aus DB loeschen
	}

	/**
	 * Reset all TRIMP- and VDOT-values in database
	 */
	private function resetTrimpAndVdot() {
		$DB        = DB::getInstance();
		$Trainings = $DB->query('SELECT `id`,`sportid`,`typeid`,`distance`,`s`,`pulse_avg` FROM `'.PREFIX.'training`')->fetchAll();

		foreach ($Trainings as $Training) {
			$DB->update('training', $Training['id'],
				array(
					'trimp',
					'vdot',
					'vdot_by_time'
				),
				array(
					Trimp::forTraining($Training),
					JD::Training2VDOT($Training['id'], $Training),
					JD::Competition2VDOT($Training['distance'], $Training['s'])
				)
			);
		}

		$this->SuccessMessages[] = 'Die Trimp- und VDOT-Werte wurden f&uuml;r <strong>'.count($Trainings).'</strong> Trainings neu berechnet.';
	}

	/**
	 * Calculate elevation
	 */
	private function calculateElevation() {
		$DB        = DB::getInstance();
		$Trainings = $DB->query('SELECT `id`,`arr_alt`,`arr_time`,`distance`,`s` FROM `'.PREFIX.'training` WHERE `arr_alt`!=""')->fetchAll();

		foreach ($Trainings as $Training) {
			$GPS    = new GpsData($Training);
			$elevationArray = $GPS->calculateElevation(true);
			$keys   = array('elevation_calculated');
			$values = array($elevationArray[0]);

			if (CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION) {
				$keys[] = 'vdot_with_elevation';
				$values[] = JD::Training2VDOTwithElevation($Training['id'], $Training, $elevationArray[1], $elevationArray[2]);
			}

			if (Request::param('overwrite') == 'true') {
				$keys[]   = 'elevation';
				$values[] = $elevationArray[0];
			}

			$DB->update('training', $Training['id'], $keys, $values);
		}

		$this->SuccessMessages[] = 'Die H&ouml;henmeter-Werte wurden f&uuml;r <strong>'.count($Trainings).'</strong> Trainings neu berechnet.';

		if (CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION)
			$this->recalculateVDOTwithElevationWithoutGPSarray();
	}

	/**
	 * Recalculate VDOT with elevation for trainings without gps array
	 */
	private function recalculateVDOTwithElevationWithoutGPSarray() {
		$DB        = DB::getInstance();
		$Trainings = $DB->query('SELECT `id`,`s`,`distance`,`elevation` FROM `'.PREFIX.'training` WHERE `elevation`>0')->fetchAll();

		foreach ($Trainings as $Training) {
			$newVdot = JD::Training2VDOTwithElevation($Training['id'], $Training, $Training['elevation'], $Training['elevation']);
			$DB->update('training', $Training['id'], 'vdot_with_elevation', $newVdot);
		}
	}

	/**
	 * Clean the databse for max_atl, max_ctl, max_trimp
	 */
	private function resetMaxValues() {
		$OldMaxValues = $this->getMaxValues();

		Trimp::calculateMaxValues();
		JD::recalculateVDOTcorrector();

		$NewMaxValues = $this->getMaxValues();

		if ($OldMaxValues == $NewMaxValues) {
			$this->SuccessMessages[] = 'An den Maximalwerten (ATL/CTL/TRIMP) und am VDOT-Korrekturfaktor hat sich nichts ge&auml;ndert.';
		} else {
			foreach (array_keys($OldMaxValues) as $Key) {
				if ($OldMaxValues[$Key] != $NewMaxValues[$Key])
					$this->SuccessMessages[] = 'Neuer '.$Key.': <strong>'.$NewMaxValues[$Key].'</strong>, alter Wert war '.$OldMaxValues[$Key];
			}
		}
	}

	/**
	 * Get max values
	 * @return array
	 */
	private function getMaxValues() {
		return array(
			'maxATL'			=> (int)Trimp::maxATL(),
			'maxCTL'			=> (int)Trimp::maxCTL(),
			'maxTRIMP'			=> (int)Trimp::maxTRIMP(),
			'VDOT-Korrektor'	=> round(JD::correctionFactor(), 4)
		);
	}

	/**
	 * Clean the databse for shoes
	 */
	private function resetShoes() {
		ShoeFactory::recalculateAllShoes();

		$this->SuccessMessages[] = 'Die Statistiken aller <strong>'.count(ShoeFactory::NamesAsArray()).'</strong> Schuhe wurden neu berechnet.';
	}
}