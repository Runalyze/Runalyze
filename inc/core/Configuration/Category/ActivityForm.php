<?php
/**
 * This file contains class::ActivityForm
 * @package Runalyze\Configuration\Category
 */

namespace Runalyze\Configuration\Category;

use Runalyze\Configuration\Fieldset;
use Runalyze\Parameter\Boolean;
use Runalyze\Parameter\Textline;
use Runalyze\Parameter\Set;
use Runalyze\Parameter\Application\ActivityCreationMode;
use Runalyze\Parameter\Application\DatabaseOrder;
use FormularInput;

/**
 * Configuration category: Activity form
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ActivityForm extends \Runalyze\Configuration\Category {
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
		$this->createHandle('FORMULAR_SHOW_SPORT', new Boolean(true));
		$this->createHandle('FORMULAR_SHOW_GENERAL', new Boolean(true));
		$this->createHandle('FORMULAR_SHOW_DISTANCE', new Boolean(true));
		$this->createHandle('FORMULAR_SHOW_SPLITS', new Boolean(true));
		$this->createHandle('FORMULAR_SHOW_WEATHER', new Boolean(true));
		$this->createHandle('FORMULAR_SHOW_OTHER', new Boolean(true));
		$this->createHandle('FORMULAR_SHOW_NOTES', new Boolean(false));
		$this->createHandle('FORMULAR_SHOW_PUBLIC', new Boolean(false));
		$this->createHandle('FORMULAR_SHOW_ELEVATION', new Boolean(false));
		$this->createHandle('FORMULAR_SHOW_GPS', new Boolean(false));
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
		$this->createHandle('TRAINING_SHOW_AFTER_CREATE', new Boolean(false));
		$this->createHandle('TRAINING_DO_ELEVATION', new Boolean(true));
		$this->createHandle('TRAINING_LOAD_WEATHER', new Boolean(true));
		$this->createHandle('PLZ', new Textline(''));
		$this->createHandle('COMPUTE_KCAL', new Boolean(true));
		$this->createHandle('COMPUTE_POWER', new Boolean(true));
		$this->createHandle('TRAINING_SORT_SPORTS', new DatabaseOrder());
		$this->createHandle('TRAINING_SORT_TYPES', new DatabaseOrder());
		$this->createHandle('TRAINING_SORT_SHOES', new DatabaseOrder());
		$this->createHandle('GARMIN_IGNORE_IDS', new Set(array()));
		$this->createHandle('DETECT_PAUSES', new Boolean(true));
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
	 * Order: equipment
	 * @return DatabaseOrder
	 */
	public function orderEquipment() {
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
	 * Automatically detect pauses
	 * @return bool
	 */
	public function detectPauses(){
		return $this->get('DETECT_PAUSES');
	}

	/**
	 * Fieldset
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		$Fieldset = new Fieldset( __('Activity form') );

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
 
		$Fieldset->addHandle( $this->handle('DETECT_PAUSES'), array(
			'label'		=> __('Detect pauses'),
			'tooltip'	=> __('Detect pauses (distance not increasing) when importing training')
		));

		$this->addHandlesForWeatherTo($Fieldset);
		$this->addHandlesForElevationTo($Fieldset);
		$this->addHandlesForSortingTo($Fieldset);

		return $Fieldset;
	}

	/**
	 * Add handles for weather to fieldset
	 * @param \Runalyze\Configuration\Fieldset $Fieldset
	 */
	private function addHandlesForWeatherTo(Fieldset &$Fieldset) {
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
	 * @param \Runalyze\Configuration\Fieldset $Fieldset
	 */
	private function addHandlesForElevationTo(Fieldset &$Fieldset) {
		$Fieldset->addHandle( $this->handle('TRAINING_DO_ELEVATION'), array(
			'label'		=> __('Automatically correct elevation data'),
			'tooltip'	=> __('Instead of using gps-elevation a correction via external services is possible.')
		));
	}

	/**
	 * Add handles for sorting to fieldset
	 * @param \Runalyze\Configuration\Fieldset $Fieldset
	 */
	private function addHandlesForSortingTo(Fieldset &$Fieldset) {
		$Fieldset->addHandle( $this->handle('TRAINING_SORT_SPORTS'), array(
			'label'		=> __('Sort: sport types')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_SORT_TYPES'), array(
			'label'		=> __('Sort: activity types')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_SORT_SHOES'), array(
			'label'		=> __('Sort: equipment')
		));
	}
}
