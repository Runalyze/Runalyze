<?php
/**
 * This file contains the class of the RunalyzePluginTool "AnalyzeVDOT".
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_AnalyzeVDOT';
/**
 * Class: RunalyzePluginTool_AnalyzeVDOT
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
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
		$this->name = __('Analyze your VDOT');
		$this->description = __('Analyze the VDOT prediction on your race results.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('Predicting your VDOT based on your training values is risky.'.
						'This plugin lists your races and compares your results with the predicted values.'.
						'This way you can get an impression of how well the prediction works for you.') );
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
		$this->Trainings = DB::getInstance()->query('
			SELECT
				id,
				time,
				sportid,
				distance,
				s,
				is_track,
				comment,
				pulse_avg,
				pulse_max,
				vdot
			FROM `'.PREFIX.'training`
			WHERE `pulse_avg`!=0 && `typeid`='.CONF_WK_TYPID.'
			ORDER BY `time` DESC')->fetchAll();
	}
}