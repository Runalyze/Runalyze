<?php
/**
 * This file contains class::TrainingObject
 * @package Runalyze\DataObjects\Training
 */
/**
 * DataObject for trainings
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @package Runalyze\DataObjects\Training
 */
class TrainingObject extends DataObject {
	/**
	 * Fill default object with standard settings and weather forecast if needed
	 */
	protected function fillDefaultObject() {
		$this->set('time', mktime(0,0,0));
		$this->set('is_public', CONF_TRAINING_MAKE_PUBLIC ? '1' : '0');

		if (CONF_TRAINING_LOAD_WEATHER)
			$this->setWeatherForecast();
	}

	/**
	 * Set weather forecast
	 */
	private function setWeatherForecast() {
		$Weather = new WeatherForecast();
		$this->set('weatherid', $Weather->id());
		$this->set('temperature', $Weather->temperature());
	}

	/**
	 * Init DatabaseScheme 
	 */
	protected function initDatabaseScheme() {
		$this->DatabaseScheme = DatabaseSchemePool::get('training/schemes/scheme.Training.php');
	}

	/**
	 * Was this training a competition?
	 * @return boolean 
	 */
	public function isCompetition() {
		return $this->get('typeid') == CONF_WK_TYPID;
	}

	/**
	 * Has this training a distance?
	 * @return boolean
	 */
	public function hasDistance() {
		return $this->get('distance') > 0;
	}

	/**
	 * Has this training gps-position-data?
	 * @return boolean
	 */
	public function hasPositionData() {
		return strlen($this->get('arr_lat')) > 0;
	}

	/**
	 * Has elevation data been corrected
	 * @return type
	 */
	public function elevationWasCorrected() {
		return $this->get('elevation_corrected') == 1;
	}

	/**
	 * Was this training a competition?
	 * @return boolean 
	 */
	static public function idIsCompetition($id) {
		return (Mysql::getInstance()->num('SELECT 1 FROM `'.PREFIX.'training` WHERE `id`='.$id.' AND `typeid`="'.CONF_WK_TYPID.'" LIMIT 1') > 0);
	}
}