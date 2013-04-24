<?php
/**
 * This file contains class::SportFactory
 * @package Runalyze\Data\Sport
 */
/**
 * Factory serving static methods for Sport
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Sport
 */
class SportFactory {
	/**
	 * All sports as array
	 * @var array
	 */
	static private $AllSports = null;

	/**
	 * Data for ID
	 * @param int $id sportid
	 * @return array
	 */
	static public function DataFor($id) {
		$Sports = self::AllSports();

		if (isset($Sports[$id]))
			return $Sports[$id];

		return self::defaultArray();
	}

	/**
	 * Array with default values
	 * 
	 * @todo This method should be useless as soon as a DatabaseScheme is used
	 * @return array
	 */
	static private function defaultArray() {
		return array(
			'name' => '?',
			'img' => '',
			'online' => 0,
			'short' => 0,
			'kcal' => 0,
			'HFavg' => 0,
			'RPE' => 0,
			'distances' => 0,
			'speed' => SportSpeed::$DEFAULT,
			'types' => 0,
			'pulse' => 0,
			'outside' => 0);
	}

	/**
	 * Get all sports
	 * @return array
	 */
	static public function AllSports() {
		if (is_null(self::$AllSports))
			self::initAllSports();

		return self::$AllSports;
	}

	/**
	 * Get all sports with types
	 * @return array
	 */
	static public function AllSportsWithTypes() {
		$Sports = self::AllSports();

		foreach ($Sports as $i => $Sport)
			if ($Sport['types'] == 0)
				unset($Sports[$i]);

		return $Sports;
	}

	/**
	 * Initialize internal sports-array from database
	 */
	static private function initAllSports() {
		if (is_null(self::$AllSports)) {
			if (CONF_TRAINING_SORT_SPORTS == 'alpha')
				$order = 'ORDER BY name ASC';
			elseif (CONF_TRAINING_SORT_SPORTS == 'id-desc')
				$order = 'ORDER BY id DESC';
			else
				$order = 'ORDER BY id ASC';

			$sports = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'sport` '.$order);
			foreach ($sports as $sport)
				self::$AllSports[$sport['id']] = $sport;
		}
	}

	/**
	 * Reinit all sports
	 * 
	 * Use this method after updating sports table
	 */
	static public function reInitAllSports() {
		self::$AllSports = null;
		self::initAllSports();
	}

	/**
	 * Get array with all names
	 * @return array ids as keys, names as values
	 */
	static public function NamesAsArray() {
		$sports = self::AllSports();
		foreach ($sports as $id => $sport)
			$sports[$id] = $sport['name'];

		return $sports;
	}

	/**
	 * Find sportid for name
	 * @param string $name
	 * @return int sportid, -1 if not found
	 */
	static public function idByName($name) {
		$Sport = Mysql::getInstance()->fetchSingle('SELECT id FROM `'.PREFIX.'sport` WHERE `name`="'.$name.'"');

		if (isset($Sport['id']))
			return $Sport['id'];

		return -1;
	}
	
	/**
	* Get normal kcal per hour
	* @param int $SportID id
	* @return int
	*/
	static public function kcalPerHourFor($SportID) {
		$Sports = self::AllSports();
		if (isset($Sports[$SportID]))
			return $Sports[$SportID]['kcal'];

		return 0;
	}

	/**
	 * Get how often the sport is used
	 * @return array ids as keys, counts as values
	 */
	static public function CountArray() {
		$Sports = Mysql::getInstance()->fetchAsArray('SELECT sportid, COUNT(sportid) as scount FROM `'.PREFIX.'training` GROUP BY sportid');
		$Counts = array();

		foreach ($Sports as $Sport)
			$Counts[$Sport['sportid']] = $Sport['scount'];

		return $Counts;
	}
	
	/**
	 * Get select-box for all sport-ids
	 * @param mixed $selected [optional] Value to be selected
	 * @return string
	 */
	static public function SelectBox($selected = -1) {
		if ($selected == -1 && isset($_POST['sportid']))
			$selected = $_POST['sportid'];

		return HTML::selectBox('sportid', self::NamesAsArray(), $selected);
	}

	/**
	 * Does this sport-id displays speed in km/h?
	 * @param int $id
	 * @return bool
	 */
	static public function usesSpeedInKmh($id) {
		$sports = self::AllSports();

		if (isset($sports[$id]))
			return ($sports[$id]['speed'] == SportSpeed::$KM_PER_H);

		return false;
	}

	/**
	 * Get speed unit for given sportid
	 * @param int $ID
	 * @return string
	 */
	static public function getSpeedUnitFor($ID) {
		$Sports = self::AllSports();

		return (isset($Sports[$ID])) ? $Sports[$ID]['speed'] : SportSpeed::$DEFAULT;
	}

	/**
	 * Get speed for a given sportid
	 * @param float $Distance
	 * @param int $Time
	 * @param int $ID
	 * @param boolean $withAppendix [optional]
	 * @param boolean $withTooltip [optional]
	 * @return string
	 */
	static public function getSpeed($Distance, $Time, $ID, $withAppendix = false, $withTooltip = false) {
		$Unit   = self::getSpeedUnitFor($ID);
		$Speed  = ($withAppendix) ? SportSpeed::getSpeedWithAppendix($Distance, $Time, $Unit) : SportSpeed::getSpeed($Distance, $Time, $Unit);

		if ($withTooltip && $Unit != SportSpeed::$DEFAULT)
			return Ajax::tooltip($Speed, SportSpeed::getSpeedWithAppendix($Distance, $Time, SportSpeed::$DEFAULT));

		return $Speed;
	}

	/**
	 * Get speed for a given sportid with appendix
	 * @param float $Distance
	 * @param int $Time
	 * @param int $ID
	 * @return string
	 */
	static public function getSpeedWithAppendix($Distance, $Time, $ID) {
		return self::getSpeed($Distance, $Time, $ID, true);
	}

	/**
	 * Get speed for a given sportid with tooltip for default unit
	 * @param float $Distance
	 * @param int $Time
	 * @param int $ID
	 * @return string
	 */
	static public function getSpeedWithTooltip($Distance, $Time, $ID) {
		return self::getSpeed($Distance, $Time, $ID, false, true);
	}

	/**
	 * Get speed for a given sportid with appendix and tooltip for default unit
	 * @param float $Distance
	 * @param int $Time
	 * @param int $ID
	 * @return string
	 */
	static public function getSpeedWithAppendixAndTooltip($Distance, $Time, $ID) {
		return self::getSpeed($Distance, $Time, $ID, true, true);
	}
}