<?php
/**
 * This file contains class::ConfigurationGeneral
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: General
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
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
	 * Create handles
	 */
	protected function createHandles() {
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
		$this->createHandle('GENDER', new Gender());
	}

	/**
	 * Gender
	 * @return Gender
	 */
	public function gender() {
		return $this->object('GENDER');
	}

	/**
	 * Create: HEART_RATE_UNIT
	 */
	protected function createHeartRateUnit() {
		$this->createHandle('HEART_RATE_UNIT', new HeartRateUnit());
	}

	/**
	 * Heart rate unit
	 * @return HeartRateUnit
	 */
	public function heartRateUnit() {
		return $this->object('HEART_RATE_UNIT');
	}

	/**
	 * Create: MAINSPORT
	 */
	protected function createMainSport() {
		$this->createHandle('MAINSPORT', new ParameterSelectRow(1, array(
			'table'			=> 'sport',
			'column'		=> 'name'
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
		$this->createHandle('RUNNINGSPORT', new ParameterSelectRow(1, array(
			'table'			=> 'sport',
			'column'		=> 'name'
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
	 * Create: TYPE_ID_RACE
	 */
	protected function createCompetitionType() {
		$this->createHandle('TYPE_ID_RACE', new ParameterSelectRow(5, array(
			'table'			=> 'type',
			'column'		=> 'name'
		)));
	}

	/**
	 * Competition type
	 * @return int
	 */
	public function competitionType() {
		return $this->get('TYPE_ID_RACE');
	}

	/**
	 * Create: TYPE_ID_LONGRUN
	 */
	protected function createLongRunType() {
		$this->createHandle('TYPE_ID_LONGRUN', new ParameterSelectRow(7, array(
			'table'			=> 'type',
			'column'		=> 'name'
		)));
	}

	/**
	 * Long run type
	 * @return int
	 */
	public function longRunType() {
		return $this->get('TYPE_ID_LONGRUN');
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('GENDER')->registerOnchangeFlag(Ajax::$RELOAD_ALL);
		$this->handle('HEART_RATE_UNIT')->registerOnchangeFlag(Ajax::$RELOAD_DATABROWSER);
		$this->handle('MAINSPORT')->registerOnchangeFlag(Ajax::$RELOAD_PAGE);
		$this->handle('RUNNINGSPORT')->registerOnchangeFlag(Ajax::$RELOAD_PAGE);
		$this->handle('TYPE_ID_RACE')->registerOnchangeFlag(Ajax::$RELOAD_PLUGINS);
		$this->handle('TYPE_ID_LONGRUN')->registerOnchangeFlag(Ajax::$RELOAD_PLUGINS);
	}

	/**
	 * Fieldset
	 * @return ConfigurationFieldset
	 */
	public function Fieldset() {
		$Fieldset = new ConfigurationFieldset( __('General settings') );

		$Fieldset->addHandle( $this->handle('GENDER'), array(
			'label'		=> __('Gender')
		));

		$Fieldset->addHandle( $this->handle('HEART_RATE_UNIT'), array(
			'label'		=> __('Heart rate unit')
		));

		$Fieldset->addHandle( $this->handle('MAINSPORT'), array(
			'label'		=> __('Main sport')
		));

		$Fieldset->addHandle( $this->handle('RUNNINGSPORT'), array(
			'label'		=> __('Running sport')
		));

		$Fieldset->addHandle( $this->handle('TYPE_ID_RACE'), array(
			'label'		=> __('Activity type: competition')
		));

		$Fieldset->addHandle( $this->handle('TYPE_ID_LONGRUN'), array(
			'label'		=> __('Activity type: long run')
		));

		return $Fieldset;
	}
}