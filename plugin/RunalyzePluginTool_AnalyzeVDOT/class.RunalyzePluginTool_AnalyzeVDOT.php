<?php
/**
 * This file contains the class of the RunalyzePluginTool "AnalyzeVDOT".
 * @package Runalyze\Plugins\Tools
 */

use Runalyze\Configuration;

$PLUGINKEY = 'RunalyzePluginTool_AnalyzeVDOT';
/**
 * Class: RunalyzePluginTool_AnalyzeVDOT
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzePluginTool_AnalyzeVDOT extends PluginTool {
	/**
	 * All trainings to be edited
	 * @var \PDOStatement
	 */
	private $Query = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Analyze your VDOT');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Analyze the VDOT prediction based on your race results.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('Predicting your VDOT based on your training values is imprecise. '.
						'This plugin lists your races and compares your results with the predicted values. '.
						'This way you can get an impression of how well the prediction works for you.') );
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->initTrainings();

		require_once __DIR__.'/TableRow.php';
		include __DIR__.'/tpl.Table.php';
	}

	/**
	 * Init internal array with all trainings
	 */
	private function initTrainings() {
		$this->Query = DB::getInstance()->query('
			SELECT
				tr.`id`,
				tr.`time`,
				tr.`sportid`,
				tr.`distance`,
				tr.`s`,
				tr.`is_track`,
				tr.`comment`,
				tr.`pulse_avg`,
				tr.`pulse_max`,
				tr.`vdot`,
				tr.`vdot_by_time`
			FROM `'.PREFIX.'raceresult` r LEFT JOIN `'.PREFIX.'training` tr ON tr.id = r.activity_id
			WHERE
				tr.`pulse_avg`!=0 AND
				tr.`sportid`='.Configuration::General()->runningSport().' AND
				r.`accountid` = '.SessionAccountHandler::getId().'
			ORDER BY tr.`time` DESC'
		);
	}
}