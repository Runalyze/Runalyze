<?php
/**
 * This file contains the class of the RunalyzePluginTool "MultiEditor".
 */
$PLUGINKEY = 'RunalyzePluginTool_MultiEditor';
/**
 * Class: RunalyzePluginTool_MultiEditor
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class RunalyzePluginTool_MultiEditor extends PluginTool {
	/**
	 * All trainings to be edited
	 * @var array
	 */
	private $Trainings = array();

	/**
	 * All key for training data
	 * @var array
	 */
	private $Keys = array();

	/**
	 * Internal array with all IDs of trainings to be edited
	 * @var array
	 */
	private $IDs = array();

	/**
	 * All errors for being displayed
	 * @var array
	 */
	private $Errors = array();

	/**
	 * All information for being displayed
	 * @var array
	 */
	private $Infos = array();


	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = 'Multi-Editor';
		$this->description = 'Bearbeitung von mehreren Trainings gleichzeitig. Notwendig f&uuml;r einige Importer.';

		$this->initPossibleKeys();
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('Dieses Tool erlaubt es, mehrere Trainings auf einmal zu bearbeiten.');
		echo HTML::p('Diese Funktionalit&auml;t ist absolut empfehlenswert f&uuml;r den Import von mehrere Trainings auf einmal.
					Nach dem Import k&ouml;nnen die Trainings direkt nacheinander bearbeitet werden.');
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		foreach ($this->Keys as $key => $Data)
			$config[$key] = array('type' => 'bool', 'var' => $Data['default'], 'description' => $Data['name'].' bearbeiten');

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->initData();
		// TODO: Select um Trainings auszuwaehlen

		include FRONTEND_PATH.'../plugin/RunalyzePluginTool_MultiEditor/tpl.Table.php';
	}

	/**
	 * Show message that some trainings have been imported, can be called from an Importer 
	 */
	public function showImportedMessage() {
		echo HTML::em('Die Trainings wurden importiert.').'<br /><br />';
	}

	/**
	 * Init all keys
	 */
	private function initPossibleKeys() {
		$this->addKey('sportid',     'Sportart', "echo Sport::getSelectBox();", true);
		$this->addKey('s',           'Dauer', "echo HTML::simpleInputField('s', 9);", true);
		$this->addKey('distance',    'Distanz', "echo HTML::simpleInputField('distance', 4);", true);
		$this->addKey('is_track',    'Bahn', "echo HTML::checkBox('is_track');", false);
		$this->addKey('pulse',       'Puls &oslash;/max', "echo HTML::simpleInputField('pulse_avg', 3).'&nbsp;'.HTML::simpleInputField('pulse_max', 3);", true);
		$this->addKey('distance',    'Distanz', "echo HTML::simpleInputField('distance', 4);", true);
		$this->addKey('kcal',        'Kalorien', "echo HTML::simpleInputField('kcal', 4);", true);
		$this->addKey('abc',         'Lauf-ABC', "echo HTML::checkBox('abc');", false);
		$this->addKey('comment',     'Bemerkung', "echo HTML::simpleInputField('comment', 30);", true);
		$this->addKey('route',       'Strecke', "echo HTML::simpleInputField('route', 30);", false);
		$this->addKey('elevation',   'hm', "echo HTML::simpleInputField('elevation', 3);", false);
		$this->addKey('partner',     'Trainingspartner', "echo HTML::simpleInputField('partner', 20);", false);
		$this->addKey('temperature', 'Temperatur', "echo HTML::simpleInputField('temperature', 2);", false);
		$this->addKey('weatherid',   'Wetter', "echo Weather::getSelectBox();", false);
		$this->addKey('clothes',     'Kleidung', "echo Clothes::getCheckboxes();", false);
		$this->addKey('splits',      'Zwischenzeiten', "echo HTML::textarea('splits', 70, 3);", false);
	}

	/**
	 * Add key to internal array
	 * @param string $key
	 * @param string $name
	 * @param string $eval
	 * @param bool $default
	 */
	private function addKey($key, $name, $eval, $default) {
		$this->Keys[$key] = array('name' => $name, 'eval' => $eval, 'default' => $default);
	}

	/**
	 * Init all data
	 */
	private function initData() {
		$this->IDs = array();

		if (isset($_GET['ids']))
			$this->IDs = explode(',', $_GET['ids']);
		if (isset($_POST['ids']))
			$this->IDs = explode(',', $_POST['ids']);

		if (empty($this->IDs)) {
			$Trainings = Mysql::getInstance()->fetchAsArray('SELECT id FROM '.PREFIX.'training ORDER BY id DESC LIMIT 5');
			foreach ($Trainings as $Data)
				$this->IDs[] = $Data['id'];
		}

		if (isset($_POST['multi']))
			$this->performUpdates();

		$this->initTrainings();
	}

	/**
	 * Init internal array with all trainings
	 */
	private function initTrainings() {
		foreach ($this->IDs as $id) {
			if ($id == Training::$CONSTRUCTOR_ID || empty($id))
				continue;

			$Training = new Training($id);

			if ($Training !== false)
				$this->Trainings[] = new Training($id);
		}
	}

	/**
	 * Perform updates for all edited trainings
	 */
	private function performUpdates() {
		$Data = $_POST['multi'];

		foreach ($Data as $id => $Info) {
			$Editor = new Editor($id, $Info);
			$Editor->performUpdate();

			$this->Errors = array_merge($this->Errors, $Editor->getErrorsAsArray());
		}

		$this->Infos[] = 'Es wurden '.count($Data).' Trainings bearbeitet.';
	}
}
?>