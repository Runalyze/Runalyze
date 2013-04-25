<?php
/**
 * This file contains class::RunalyzePluginTool_MultiEditor
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_MultiEditor';
/**
 * Plugin "MultiEditor"
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzePluginTool_MultiEditor extends PluginTool {
	/**
	 * Internal array with all IDs of trainings to be edited
	 * @var array
	 */
	private $IDs = array();

	/**
	 * Number of trainings to display
	 * @var int
	 */
	static private $NUMBER_OF_TRAININGS_TO_DISPLAY = 20;

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

		return $config;
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		
	}

	/**
	 * Includes the plugin-file for displaying the tool
	 */
	public function display() {
		$this->prepareForDisplay();

		$this->displayContent();
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->initData();

		$MultiEditor = new MultiEditor($this->IDs);
		$MultiEditor->display();

		echo Ajax::wrapJS('$("#ajax").addClass("smallWin");');
	}

	/**
	 * Init all data
	 */
	private function initData() {
		$this->IDs = array();

		if (strlen(Request::param('ids')) > 0) {
			$this->IDs = explode(',', Request::param('ids'));
		} else {
			$Result = Mysql::getInstance()->fetchAsArray('SELECT id FROM `'.PREFIX.'training` ORDER BY `id` DESC LIMIT '.self::$NUMBER_OF_TRAININGS_TO_DISPLAY);
			foreach ($Result as $Data)
				$this->IDs[] = $Data['id'];
		}
	}
}