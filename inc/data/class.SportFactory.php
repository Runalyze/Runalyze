<?php
/**
 * This file contains class::SportFactory
 * @package Runalyze\Data\Sport
 */

use Runalyze\Configuration;
use Runalyze\Activity\Pace;

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
	 * Get icon options
	 * @return array
	 */
	static public function getIconOptions() {
		$Files = array(
			'icons8-Sports-Mode',
			'icons8-Running',
			'icons8-Regular-Biking',
			'icons8-Swimming',
			'icons8-Yoga',
			// New in v2.1
			'icons8-Climbing',
			'icons8-Dancing',
			'icons8-Exercise',
			'icons8-Football',
			'icons8-Guru',
			'icons8-Handball',
			'icons8-Mountain-Biking',
			'icons8-Paddling',
			'icons8-Pilates',
			'icons8-Pushups',
			'icons8-Regular-Biking',
			'icons8-Roller-Skating',
			'icons8-Rowing',
			'icons8-Time-Trial-Biking',
			'icons8-Trekking',
			'icons8-Walking',
			'icons8-Weightlift',
			'icons8-skiing',
		);

		$Options = array();
		foreach ($Files as $File)
			$Options[$File] = $File;

		return $Options;
	}

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
			'short' => 0,
			'kcal' => 600,
			'HFavg' => 140,
			'distances' => 0,
			'speed' => Pace::STANDARD,
			'types' => 0,
			'power'	=> 0,
			'outside' => 0
		);
	}

	/**
	 * Get all sports
	 * @return array
	 */
	static public function AllSports() {
		if (is_null(self::$AllSports)) {
			self::initAllSports();
		}

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
		self::$AllSports = array();
		$sports = self::cacheAllSports();

		foreach ($sports as $sport) {
			self::$AllSports[(string)$sport['id']] = $sport;
		}

		Configuration::ActivityForm()->orderSports()->sort(self::$AllSports);
	}

	/**
	 * Cache all sports for user
	 */
	static private function cacheAllSports() {
		$sports = Cache::get('sport');

		if (is_null($sports)) {
			$sports = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'sport` WHERE `accountid` = '.SessionAccountHandler::getId())->fetchAll();
			Cache::set('sport', $sports, '3600');
		}

		return $sports;
	}

	/**
	 * Reinit all sports
	 *
	 * Use this method after updating sports table
	 */
	static public function reInitAllSports() {
		Cache::delete('sport');

		self::initAllSports();
	}

	/**
	 * Get array with all names
	 * @return array ids as keys, names as values
	 */
	static public function NamesAsArray() {
		$sports = self::AllSports();

		foreach ($sports as $id => $sport) {
			$sports[$id] = $sport['name'];
		}

		return $sports;
	}

	/**
	 * Find sportid for name
	 * @param string $name
	 * @return int sportid, -1 if not found
	 */
	static public function idByName($name) {
		$sports = self::cacheAllSports();

		foreach ($sports as $sport) {
			if ($sport['name'] == $name) {
				return $sport['id'];
			}
		}

		return -1;
	}

	/**
	* Get normal kcal per hour
	* @param int $SportID id
	* @return int
	*/
	static public function kcalPerHourFor($SportID) {
		$Sports = self::AllSports();

		if (isset($Sports[$SportID])) {
			return $Sports[$SportID]['kcal'];
		}

		return 0;
	}

	/**
	 * Name of sport
	 * @param string $sportid
	 * @return string
	 */
	static public function name($sportid) {
		$Sports = self::AllSports();

		if (isset($Sports[$sportid])) {
			return $Sports[$sportid]['name'];
		}

		return __('unknown');
	}

	/**
	 * Get how often the sport is used
	 * @return array ids as keys, counts as values
	 */
	static public function CountArray() {
		$Sports = DB::getInstance()->query('SELECT sportid, COUNT(sportid) as scount FROM `'.PREFIX.'training` WHERE `accountid` = '.SessionAccountHandler::getId().' GROUP BY sportid')->fetchAll();
		$Counts = array();

		foreach ($Sports as $Sport) {
			$Counts[$Sport['sportid']] = $Sport['scount'];
		}

		return $Counts;
	}

	/**
	 * Get speed unit for given sportid
	 * @param int $ID
	 * @return string
	 */
	static public function getSpeedUnitFor($ID) {
		$Sports = self::AllSports();

		return (isset($Sports[$ID])) ? $Sports[$ID]['speed'] : Pace::STANDARD;
	}
}
