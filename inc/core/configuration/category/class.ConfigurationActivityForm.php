<?php
/**
 * This file contains class::ConfigurationActivityForm
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: Activity form
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ConfigurationActivityForm extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'activity-form';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('FORMULAR_SHOW_SPORT', new ParameterBool(true));
		$this->createHandle('FORMULAR_SHOW_GENERAL', new ParameterBool(true));
		$this->createHandle('FORMULAR_SHOW_DISTANCE', new ParameterBool(true));
		$this->createHandle('FORMULAR_SHOW_SPLITS', new ParameterBool(true));
		$this->createHandle('FORMULAR_SHOW_WEATHER', new ParameterBool(true));
		$this->createHandle('FORMULAR_SHOW_OTHER', new ParameterBool(true));
		$this->createHandle('FORMULAR_SHOW_NOTES', new ParameterBool(false));
		$this->createHandle('FORMULAR_SHOW_PUBLIC', new ParameterBool(false));
		$this->createHandle('FORMULAR_SHOW_ELEVATION', new ParameterBool(false));
		$this->createHandle('FORMULAR_SHOW_GPS', new ParameterBool(false));
	}

	/**
	 * Show ...?
	 * @param string $Key possible values: SPORT, GENERAL, DISTANCE, SPLITS,
	 * WEATHER, OTHER, NOTES, PUBLIC, ELEVATION, GPS
	 * @return bool
	 */
	public function show($Key) {
		return $this->get('FORMULAR_SHOW_'.$Key);
	}

	/**
	 * Update ...
	 * @param string $Key possible values: SPORT, GENERAL, DISTANCE, SPLITS,
	 * WEATHER, OTHER, NOTES, PUBLIC, ELEVATION, GPS
	 * @param bool $flag
	 */
	public function update($Key, $flag) {
		$this->object('FORMULAR_SHOW_'.$Key)->set($flag);
		$this->updateValue( $this->object('FORMULAR_SHOW_'.$Key) );
	}
}