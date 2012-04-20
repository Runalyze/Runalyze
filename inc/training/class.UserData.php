<?php
/**
 * This file contains the class::Userata for handling user-information
 */
/**
 * Class: UserData
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class UserData extends DataObject {	
	/**
	* Array containing all rows from database
	* @var array
	*/
	static private $fullArray = null;

	/**
	 * Init DatabaseScheme 
	 */
	protected function initDatabaseScheme() {
		$this->DatabaseScheme = DatabaseSchemePool::get('training/schemes/scheme.UserData.php');

		$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Sportler');
		$PluginConfiguration = $Plugin->get('config');

		if (!$PluginConfiguration['use_body_fat']['var'])
			$this->DatabaseScheme->hideFieldset('analyse');

		if (!$PluginConfiguration['use_pulse']['var'])
			$this->DatabaseScheme->hideFieldset('pulse');
	}

	/**
	 * Set current timestamp, needed for creation-formular 
	 */
	public function setCurrentTimestamp() {
		$this->set('time', time());
	}

	/**
	 * Get timestamp of data
	 * @return int
	 */
	public function getTimestamp() {
		return $this->get('time');
	}

	/**
	 * Get date
	 * @return string
	 */
	public function getDate() {
		return date('d.m.Y', $this->getTimestamp());
	}
	
	/**
	* Get weight
	* @return float
	*/
	public function getWeight() {
		return $this->get('weight');
	}
	
	/**
	* Get pulse in rest
	* @return int
	*/
	public function getPulseRest() {
		return $this->get('pulse_rest');
	}
	
	/**
	* Get max. pulse
	* @return int
	*/
	public function getPulseMax() {
		return $this->get('pulse_max');
	}
	
	/**
	* Get body fat
	* @return float
	*/
	public function getBodyFat() {
		return $this->get('fat');
	}
	
	/**
	* Get water
	* @return float
	*/
	public function getWater() {
		return $this->get('water');
	}
	
	/**
	* Get muscles
	* @return float
	*/
	public function getMuscles() {
		return $this->get('muscles');
	}

	/**
	 * Get all rows from user-data
	 * @return array
	 */
	static public function getFullArray() {
		if (is_null(self::$fullArray))
			self::$fullArray = Mysql::getInstance()->fetchAsArray('SELECT * FROM '.PREFIX.'user ORDER BY `time` ASC');

		return self::$fullArray;
	}

	/**
	 * Is the user male?
	 * @return boolean
	 */
	static public function isMale() {
		return (CONF_GENDER == 'm');
	}

	/**
	 * Is the user female?
	 * @return boolean
	 */
	static public function isFemale() {
		return (CONF_GENDER == 'f');
	}
}