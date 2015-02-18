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
	 * Number of trainings to display
	 * @var int
	 */
	const NUMBER_OF_TRAININGS_TO_DISPLAY = 20;

	/**
	 * Internal array with all IDs of trainings to be edited
	 * @var array
	 */
	private $IDs = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Multi editor');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Edit a couple of activities. This plugin is needed to upload more than one activity at once.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('This plugin allows you to edit multiple activities one after another.') );
		echo HTML::warning( __('At the moment it\'s not possible to edit multiple activities with only one form.') );
	}

	/**
	 * Includes the plugin-file for displaying the tool
	 */
	public function display() {
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

		echo Ajax::wrapJS('$("#ajax").addClass("small-window");');
	}

	/**
	 * Init all data
	 */
	private function initData() {
		$this->IDs = array();

		if (strlen(Request::param('ids')) > 0) {
			$this->IDs = explode(',', Request::param('ids'));
		} else {
			$this->IDs = DB::getInstance()->query(
				'SELECT id FROM `'.PREFIX.'training`
				ORDER BY `id` DESC
				LIMIT '.self::NUMBER_OF_TRAININGS_TO_DISPLAY
			)->fetchAll(PDO::FETCH_COLUMN, 0);
		}
	}
}