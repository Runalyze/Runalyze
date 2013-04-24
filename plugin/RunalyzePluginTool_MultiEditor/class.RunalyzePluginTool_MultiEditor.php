<?php
/**
 * This file contains the class of the RunalyzePluginTool "MultiEditor".
 */
$PLUGINKEY = 'RunalyzePluginTool_MultiEditor';
/**
 * Class: RunalyzePluginTool_MultiEditor
 * @author Hannes Christiansen
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
	 * Boolean flag: Keys have been set
	 * @var boolean
	 */
	static public $KEYS_ARE_SET = false;


	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = 'Multi-Editor';
		$this->description = 'Bearbeitung von mehreren Trainings gleichzeitig. Notwendig f&uuml;r einige Importer.';
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

		//$this->initPossibleKeys();

		//foreach ($this->Keys as $key => $Data)
		//	$config[$key] = array('type' => 'bool', 'var' => $Data['default'], 'description' => $Data['name'].' bearbeiten');

		return $config;
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		//$this->initPossibleKeys();
	}

	/**
	 * Includes the plugin-file for displaying the tool
	 */
	public function display() {
		$this->prepareForDisplay();

		//$this->displayHeader();
		$this->displayContent();
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->initData();
		// TODO: Select um Trainings auszuwaehlen

		$MultiEditor = new MultiEditor($this->IDs);
		$MultiEditor->display();

		echo Ajax::wrapJS('$("#ajax").addClass("smallWin");');
		//include FRONTEND_PATH.'../plugin/RunalyzePluginTool_MultiEditor/tpl.Table.php';
	}

	/**
	 * Show message that some trainings have been imported, can be called from an Importer 
	 */
	public function showImportedMessage() {
		//echo HTML::em('Die Trainings wurden importiert.').'<br /><br />';
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
			$Result = Mysql::getInstance()->fetchAsArray('SELECT id FROM `'.PREFIX.'training` ORDER BY `id` DESC LIMIT 20');
			foreach ($Result as $Data)
				$this->IDs[] = $Data['id'];
		}
	}
}