<?php
/**
 * This file contains the class of the RunalyzePluginTool "AnalyzeVDOT".
 */
$PLUGINKEY = 'RunalyzePluginTool_AnalyzeVDOT';
/**
 * Class: RunalyzePluginTool_AnalyzeVDOT
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginTool
 * @uses class::Mysql
 * @uses class::Helper
 * @uses class::Draw
 */
class RunalyzePluginTool_AnalyzeVDOT extends PluginTool {
	/**
	 * All trainings to be edited
	 * @var array
	 */
	private $Trainings = array();


	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = 'VDOT analysieren';
		$this->description = 'Den VDOT im Zusammenhang mit Wettkampfergebnissen analysieren';
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
		$this->initTrainings();

		include FRONTEND_PATH.'../plugin/'.$this->key.'/tpl.Table.php';
	}

	/**
	 * Init internal array with all trainings
	 */
	private function initTrainings() {
		$this->Trainings = Mysql::getInstance()->fetchAsArray('
			SELECT * FROM `'.PREFIX.'training`
			WHERE `pulse_avg`!=0 && `typeid`='.CONF_WK_TYPID.'
			ORDER BY `time` ASC');
	}
}
?>