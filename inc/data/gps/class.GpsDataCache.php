<?php
/**
 * This file contains class::GpsDataCache
 * @package Runalyze\Data\GPS
 */
/**
 * Cache for GPS data
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS
 */
class GpsDataCache {
	/**
	 * Internal array with all cached data
	 * @var array 
	 */
	protected $Array = array();

	/**
	 * ID of corresponding training
	 * @var int
	 */
	protected $TrainingID = 0;

	/**
	 * Internal flag: is empty
	 * @var boolean
	 */
	protected $isEmpty = true;

	/**
	 * Construct new GpsDataCache-object
	 * @param type $TrainingID
	 * @param mixed $String [optional]
	 */
	public function __construct($TrainingID, $String = null) {
		$this->TrainingID = $TrainingID;

		if ($String === false)
			return;

		if (is_null($String))
			$this->readFromDb();
		else
			$this->readFromDbString($String);
	}

	/**
	 * Is the cache empty?
	 * @return boolean
	 */
	public function isEmpty() {
		return $this->isEmpty;
	}

	/**
	 * Save cache object to database 
	 */
	public function saveInDatabase() {
		DB::getInstance()->update('training', $this->TrainingID, 'gps_cache_object', $this->getDbString());

		$this->isEmpty = empty($this->Array);
	}

	/**
	 * Read from database 
	 */
	protected function readFromDb() {
		$Data = DB::getInstance()->query('SELECT `gps_cache_object` FROM '.PREFIX.'training WHERE `id`='.(int)$this->TrainingID.' LIMIT 1')->fetch();

		$this->readFromDbString($Data['gps_cache_object']);
	}

	/**
	 * Set internal array from database
	 * @param string $String 
	 */
	protected function readFromDbString($String) {
		$this->Array = unserialize($String);

		$this->isEmpty = empty($this->Array);
	}

	/**
	 * Get internal data as string
	 * @return string 
	 */
	protected function getDbString() {
		return serialize($this->Array);
	}

	/**
	 * Get value
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		if (isset($this->Array[$key]))
			return $this->Array[$key];

		return null;
	}

	/**
	 * Set value
	 * @param string $key
	 * @param mixed $value 
	 */
	public function set($key, $value) {
		$this->Array[$key] = $value;
	}
}