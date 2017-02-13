<?php
/**
 * This file contains the class::UserData
 * @package Runalyze\DataObjects
 */
/**
 * Class: UserData
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects
 */
class UserData extends DataObject {
	/**
	* Array containing all rows from database
	* @var array
	*/
	private static $fullArray = null;

	/**
	 * Init DatabaseScheme 
	 */
	protected function initDatabaseScheme() {
		$this->DatabaseScheme = DatabaseSchemePool::get('training/schemes/scheme.UserData.php');

		$Factory = new PluginFactory();

		if ($Factory->isInstalled('RunalyzePluginPanel_Sportler')) {
			$Plugin = $Factory->newInstance('RunalyzePluginPanel_Sportler');

		}
	}

	/**
	 * Tasks to perform after insert
	 */
	protected function tasksAfterInsert() {
		Cache::delete(self::CACHE_KEY);

		Helper::recalculateHFmaxAndHFrest();
	}

	/**
	 * Tasks to perform after update
	 */
	protected function tasksAfterUpdate() {
		Cache::delete(self::CACHE_KEY);

		Helper::recalculateHFmaxAndHFrest();
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
	* Get sleep duration
	* @return float
	*/
	public function getSleepDuration() {
		return $this->get('sleep_duration');
	}

	/**
	* Get note
	* @return float
	*/
	public function getNote() {
		return $this->get('notes');
	}
        
}
