<?php
/**
 * This file contains class::ConfigurationGeneral
 * @package Runalyze\System\Configuration\Category
 */
/**
 * Configuration category: General
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Category
 */
class ConfigurationGeneral extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'general';
	}

	/**
	 * Title
	 * @return string
	 */
	public function title() {
		return __('General');
	}

	/**
	 * Create values
	 */
	protected function createValues() {
		$this->createGender();
		$this->createHeartRateUnit();
		$this->createMainSport();
		$this->createRunningSport();
		$this->createCompetitionType();
		$this->createLongRunType();
	}

	/**
	 * Create: GENDER
	 */
	protected function createGender() {
		$this->createValue(new Gender('GENDER'));
	}

	/**
	 * Gender
	 * @return Gender
	 */
	public function gender() {
		return $this->object('GENDER');
	}

	/**
	 * Create: PULS_MODE
	 */
	protected function createHeartRateUnit() {
		$this->createValue(new HeartRateUnit('PULS_MODE'));
	}

	/**
	 * Heart rate unit
	 * @return HeartRateUnit
	 */
	public function heartRateUnit() {
		return $this->object('PULS_MODE');
	}

	/**
	 * Create: MAINSPORT
	 */
	protected function createMainSport() {
		$this->createValue(new ConfigurationValueSelectDB('MAINSPORT', array(
			'default'		=> 1,
			'label'			=> __('Main sport'),
			'table'			=> 'sport',
			'column'		=> 'name',
			'onchange'		=> Ajax::$RELOAD_PAGE
		)));
	}

	/**
	 * Main sport
	 * @return int
	 */
	public function mainSport() {
		return $this->get('MAINSPORT');
	}

	/**
	 * Create: RUNNINGSPORT
	 */
	protected function createRunningSport() {
		$this->createValue(new ConfigurationValueSelectDB('RUNNINGSPORT', array(
			'default'		=> 1,
			'label'			=> __('Running sport'),
			'table'			=> 'sport',
			'column'		=> 'name',
			'onchange'		=> Ajax::$RELOAD_PAGE
		)));
	}

	/**
	 * Running sport
	 * @return int
	 */
	public function runningSport() {
		return $this->get('RUNNINGSPORT');
	}

	/**
	 * Create: WK_TYPID
	 */
	protected function createCompetitionType() {
		$this->createValue(new ConfigurationValueSelectDB('WK_TYPID', array(
			'default'		=> 5,
			'label'			=> __('Activity type: competition'),
			'table'			=> 'type',
			'column'		=> 'name',
			'onchange'		=> Ajax::$RELOAD_PLUGINS
		)));
	}

	/**
	 * Competition type
	 * @return int
	 */
	public function competitionType() {
		return $this->get('WK_TYPID');
	}

	/**
	 * Create: LL_TYPID
	 */
	protected function createLongRunType() {
		$this->createValue(new ConfigurationValueSelectDB('LL_TYPID', array(
			'default'		=> 7,
			'label'			=> __('Activity type: long run'),
			'table'			=> 'type',
			'column'		=> 'name',
			'onchange'		=> Ajax::$RELOAD_PLUGINS
		)));
	}

	/**
	 * Long run type
	 * @return int
	 */
	public function longRunType() {
		return $this->get('LL_TYPID');
	}
}