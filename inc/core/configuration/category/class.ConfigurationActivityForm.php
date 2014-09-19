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
		$this->createHandlesForLegends();
		$this->createHandlesForSettings();
	}

	/**
	 * Create handles for legends
	 */
	private function createHandlesForLegends() {
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

	/**
	 * Create handles for other settings
	 */
	private function createHandlesForSettings() {
		$this->createHandle('TRAINING_CREATE_MODE', new ActivityCreationMode());
		$this->createHandle('TRAINING_SHOW_AFTER_CREATE', new ParameterBool(false));
		$this->createHandle('TRAINING_DO_ELEVATION', new ParameterBool(true));
		$this->createHandle('TRAINING_ELEVATION_SERVER', new ElevationServer());
		$this->createHandle('TRAINING_LOAD_WEATHER', new ParameterBool(true));
		$this->createHandle('PLZ', new ParameterString());
		$this->createHandle('COMPUTE_KCAL', new ParameterBool(true));
		$this->createHandle('COMPUTE_POWER', new ParameterBool(true));
		$this->createHandle('TRAINING_SORT_SPORTS', new DatabaseOrder());
		$this->createHandle('TRAINING_SORT_TYPES', new DatabaseOrder());
		$this->createHandle('TRAINING_SORT_SHOES', new DatabaseOrder());
		$this->createHandle('GARMIN_IGNORE_IDS', new ParameterArray());
	}

	/**
	 * Creation mode
	 * @return ActivityCreationMode
	 */
	public function creationMode() {
		return $this->object('TRAINING_CREATE_MODE');
	}

	/**
	 * Show activity after creation?
	 * @return bool
	 */
	public function showActivity() {
		return $this->get('TRAINING_SHOW_AFTER_CREATE');
	}

	/**
	 * Correct elevation data?
	 * @return bool
	 */
	public function correctElevation() {
		return $this->get('TRAINING_DO_ELEVATION');
	}

	/**
	 * Server for elevation correction
	 * @return ElevationServer
	 */
	public function elevationServer() {
		return $this->object('TRAINING_ELEVATION_SERVER');
	}

	/**
	 * Load weather forecast?
	 * @return bool
	 */
	public function loadWeather() {
		return $this->get('TRAINING_LOAD_WEATHER');
	}

	/**
	 * Location for weather forecast
	 * @return string
	 */
	public function weatherLocation() {
		return $this->get('PLZ');
	}

	/**
	 * Compute calories
	 * @return bool
	 */
	public function computeCalories() {
		return $this->get('COMPUTE_KCAL');
	}

	/**
	 * Compute power
	 * @return bool
	 */
	public function computePower() {
		return $this->get('COMPUTE_POWER');
	}

	/**
	 * Order: sports
	 * @return DatabaseOrder
	 */
	public function orderSports() {
		return $this->object('TRAINING_SORT_SPORTS');
	}

	/**
	 * Order: types
	 * @return DatabaseOrder
	 */
	public function orderTypes() {
		return $this->object('TRAINING_SORT_TYPES');
	}

	/**
	 * Order: shoes
	 * @return DatabaseOrder
	 */
	public function orderShoes() {
		return $this->object('TRAINING_SORT_SHOES');
	}

	/**
	 * Ignored activity IDs from Garmin
	 * @return array
	 */
	public function ignoredActivityIDs() {
		return $this->get('GARMIN_IGNORE_IDS');
	}

	/**
	 * Add new ID to ignored ones
	 * @param string $ID
	 */
	public function ignoreActivityID($ID) {
		$this->object('GARMIN_IGNORE_IDS')->append($ID);
		$this->updateValue( $this->object('GARMIN_IGNORE_IDS') );
	}
}