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
	 * @var string
	 */
	const CACHE_KEY = 'userdata';

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

			if (!$Plugin->Configuration()->value('use_body_fat'))
				$this->DatabaseScheme->hideFieldset('analyse');

			if (!$Plugin->Configuration()->value('use_pulse'))
				$this->DatabaseScheme->hideField('pulse_rest');
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
        
	/**
	 * Get all rows from user-data
	 * @return array
	 */
	public static function getFullArray() {
		if (!is_null(self::$fullArray)) {
			return self::$fullArray;
		}

		$userdata = Cache::get(self::CACHE_KEY);

		if (is_null($userdata)) {
			$userdata = DB::getInstance()->query('SELECT * FROM '.PREFIX.'user WHERE accountid = '.SessionAccountHandler::getId().' ORDER BY `time` ASC')->fetchAll();
			Cache::set(self::CACHE_KEY, $userdata, '600');
		}

		self::$fullArray = $userdata;

		return $userdata;
	}
}