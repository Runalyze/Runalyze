<?php
/**
 * This file contains the class of the RunalyzePluginTool "JDTables".
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_JDTables';
/**
 * Class: RunalyzePluginTool_JDTables
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzePluginTool_JDTables extends PluginTool {
	/**
	 * Specific range for current table
	 * @var array
	 */
	private $Range = array();

	/**
	 * Tables
	 * @var array
	 */
	private $Tables = array();

	/**
	 * Paces
	 * @var array
	 */
	private $Paces = array();

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->name = __('Tables by Jack Daniels');
		$this->description = __('Tables for heart rate, paces and VDOT values by Jack Daniels');

		$this->setTables();
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		$config['pace_distances'] = array(
			'type' => 'array',
			'var' => array(0.2, 0.4, 1, 3, 5, 10, 21.1, 42.2, 50),
			'description' => Ajax::tooltip(__('Distances'), __('comma seperated'))
		);
		$config['vdot_range']     = array(
			'type' => 'array',
			'var' => array(30, 80),
			'description' => Ajax::tooltip(__('VDOT from ... to ...'), __('two values, comma seperated'))
		);
		$config['pace_range']     = array(
			'type' => 'array',
			'var' => array(60, 180),
			'description' => Ajax::tooltip(__('Pace table: 400m from  ...s to ...s'), __('two values, comma seperated'))
		);

		return $config;
	}

	/**
	 * Set tables
	 */
	private function setTables() {
		$this->Tables = array(
			'prognosis'	=> array(
				'title'	=> __('VDOT values with equivalent race results'),
				'hint'	=> __('Predict results on different distances for a given VDOT.'),
				'init'	=> '$this->initVDOTRange();',
				'tpl'	=> 'tpl.PrognosisTable.php'
			),
			'vdot-paces'	=> array(
				'title'	=> __('VDOT values with equivalent paces'),
				'hint'	=> __('Find your training paces in min/km for given VDOT values.'),
				'init'	=> '$this->initVDOTRange();$this->initPaces();',
				'tpl'	=> 'tpl.VDOTPaceTable.php'
			),
			'pace'	=> array(
				'title'	=> __('General pace table'),
				'hint'	=> __('This table shows times for different distances if you run them with the same pace.'),
				'init'	=> '$this->initPaceRange();',
				'tpl'	=> 'tpl.PaceTable.php'
			)
		);
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->showListOfTables();

		if (array_key_exists(Request::param('table'), $this->Tables))
			$this->showTable(Request::param('table'));
	}

	/**
	 * Show table
	 * @param string $tableKey
	 */
	private function showTable($tableKey) {
		eval($this->Tables[$tableKey]['init']);

		echo HTML::p('');

		include FRONTEND_PATH.'../plugin/'.$this->key.'/'.$this->Tables[$tableKey]['tpl'];
	}

	/**
	 * Show list of tables
	 */
	private function showListOfTables() {
		foreach ($this->Tables as $key => $data) {
			$Title = '<strong class="block">'.$data['title'].'</strong>';
			$Info = '<small class="block">'.$data['hint'].'</small>';

			if ($key == Request::param('table'))
				echo '<p class="okay">'.$this->getActionLink($Title.' '.$Info, 'table='.$key).'</p>';
			else
				echo HTML::fileBlock( $this->getActionLink($Title.' '.$Info, 'table='.$key) );
		}
	}

	/**
	 * Init paces
	 */
	private function initPaces() {
		// TODO: Check why 'percent' values have to be different to get the table match!!!
		$this->Paces = array(
			'L-Tempo'	=> array('from' => 59, 'to' => 74, 'percent' => 72.5),
			'M-Tempo'	=> array('from' => 75, 'to' => 84, 'percent' => 86),
			'S-Tempo'	=> array('from' => 83, 'to' => 88, 'percent' => 90),
			'I-Tempo'	=> array('from' => 95, 'to' => 100, 'percent' => 97.5),
			'W-Tempo'	=> array('from' => 105, 'to' => 110, 'percent' => 107)
		);
	}

	/**
	 * Init pace range
	 */
	private function initVDOTRange() {
		$min = 30;
		$max = 80;

		if (count($this->config['vdot_range']['var']) == 2) {
			$min = min($this->config['vdot_range']['var']);
			$max = max($this->config['vdot_range']['var']);
		}

		$this->Range = range($min, $max);
	}

	/**
	 * Init pace range
	 */
	private function initPaceRange() {
		$min = 60;
		$max = 180;

		if (count($this->config['pace_range']['var']) == 2) {
			$min = min($this->config['pace_range']['var']);
			$max = max($this->config['pace_range']['var']);
		}

		$this->Range = range($min, $max);
	}
}