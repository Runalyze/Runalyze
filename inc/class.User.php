<?php
/**
 * This file contains the class::User for handling user-information
 */
/**
 * Class: User
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
 */
class User {
	/**
	 * Array containing last row from database
	 * @var array
	 */
	static private $lastRow = null;
	
	/**
	* Array containing all rows from database
	* @var array
	*/
	static private $fullArray = null;

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Destructor
	 */
	public function __destruct() {}

	/**
	 * Get internal array with all data rows
	 * @return array
	 */
	static public function getLastRow() {
		if (is_null(self::$lastRow)) {
			self::$lastRow = Mysql::getInstance()->fetchSingle('SELECT * FROM '.PREFIX.'user ORDER BY time DESC');

			if (self::$lastRow === false) {
				self::$lastRow = self::getDefaultArray();
				Error::getInstance()->addNotice('No user-data available. Set default array.');
			}
		}

		return self::$lastRow;
	}

	/**
	 * Get all rows from user-data
	 * @return array
	 */
	static public function getFullArray() {
		if (is_null(self::$fullArray))
			self::$fullArray = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'user` ORDER BY `time` ASC');

		return self::$fullArray;
	}

	/**
	 * Get array with default values for a row
	 * @return array
	 */
	static public function getDefaultArray() {
		return array('time' => time(), 'weight' => 0, 'pulse_rest' => 60, 'pulse_max' => 200,
			'fat' => 0, 'water' => 0, 'muscles' => 0);
	}

	/**
	 * Get current weight 
	 * @return double
	 */
	static public function getCurrentWeight() {
		$lastData = self::getLastRow();

		return $lastData['weight'];
	}
	
	/**
	* Get current pulse at rest
	* @return int
	*/
	static public function getCurrentRestPulse() {
		$lastData = self::getLastRow();

		return $lastData['pulse_rest'];
	}
	
	/**
	* Get current max pulse
	* @return int
	*/
	static public function getCurrentMaxPulse() {
		$lastData = self::getLastRow();

		return $lastData['pulse_max'];
	}
}
?>