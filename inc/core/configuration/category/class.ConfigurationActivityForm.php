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
		$this->object('FORMULAR_SHOW_'.$Key)->setFromString($flag);
		$this->updateValue( $this->handle('FORMULAR_SHOW_'.$Key) );
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
		$this->createHandle('PLZ', new ParameterString(''));
		$this->createHandle('COMPUTE_KCAL', new ParameterBool(true));
		$this->createHandle('COMPUTE_POWER', new ParameterBool(true));
		$this->createHandle('TRAINING_SORT_SPORTS', new DatabaseOrder());
		$this->createHandle('TRAINING_SORT_TYPES', new DatabaseOrder());
		$this->createHandle('TRAINING_SORT_SHOES', new DatabaseOrder());
		$this->createHandle('GARMIN_IGNORE_IDS', new ParameterArray(array()));
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
		$this->updateValue( $this->handle('GARMIN_IGNORE_IDS') );
	}

	/**
	 * Fieldset
	 * @return ConfigurationFieldset
	 */
	public function Fieldset() {
		$Fieldset = new ConfigurationFieldset( __('Activity form') );

		$Fieldset->addHandle( $this->handle('TRAINING_CREATE_MODE'), array(
			'label'		=> __('Default window')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_SHOW_AFTER_CREATE'), array(
			'label'		=> __('Show activity after creation')
		));

		$Fieldset->addHandle( $this->handle('COMPUTE_KCAL'), array(
			'label'		=> __('Calculate calories'),
			'tooltip'	=> __('Recalculate calories after changing duration by hand')
		));

		$Fieldset->addHandle( $this->handle('COMPUTE_POWER'), array(
			'label'		=> __('Calculate power'),
			'tooltip'	=> __('Calculate power by speed and grade for cycling')
		));

		$this->addHandlesForWeatherTo($Fieldset);
		$this->addHandlesForElevationTo($Fieldset);
		$this->addHandlesForSortingTo($Fieldset);

		return $Fieldset;
	}

	/**
	 * Add handles for weather to fieldset
	 * @param FormularFieldset $Fieldset
	 */
	private function addHandlesForWeatherTo(FormularFieldset &$Fieldset) {
		$Fieldset->addHandle( $this->handle('TRAINING_LOAD_WEATHER'), array(
			'label'		=> __('Automatically load weather conditions'),
			'tooltip'	=> __('via openweathermap.org')
		));

		$Fieldset->addHandle( $this->handle('PLZ'), array(
			'label'		=> __('for weather: Location'),
			'tooltip'	=> __('For loading weather data from openweathermap.org<br>e.g. <em>Berlin, de</em>'),
			'size'		=> FormularInput::$SIZE_MIDDLE
		));
	}

	/**
	 * Add handles for elevation to fieldset
	 * @param FormularFieldset $Fieldset
	 */
	private function addHandlesForElevationTo(FormularFieldset &$Fieldset) {
		$Fieldset->addHandle( $this->handle('TRAINING_DO_ELEVATION'), array(
			'label'		=> __('Automatically correct elevation data'),
			'tooltip'	=> __('Instead of using gps-elevation a correction via external services is possible.')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_ELEVATION_SERVER'), array(
			'label'		=> __('for elevation correction: server'),
			'tooltip'	=> __('By default, local srtm-files are used. If they are not available, an external server is used.')
		));
	}

	/**
	 * Add handles for sorting to fieldset
	 * @param FormularFieldset $Fieldset
	 */
	private function addHandlesForSortingTo(FormularFieldset &$Fieldset) {
		$Fieldset->addHandle( $this->handle('TRAINING_SORT_SPORTS'), array(
			'label'		=> __('Sort: sport types')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_SORT_TYPES'), array(
			'label'		=> __('Sort: activity types')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_SORT_SHOES'), array(
			'label'		=> __('Sort: shoes')
		));
	}
}