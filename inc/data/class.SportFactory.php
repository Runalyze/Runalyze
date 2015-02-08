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
			'icons8-sports_mode',
			'icons8-running',
			'icons8-regular_biking',
			'icons8-swimming',
			'icons8-yoga',
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
			'RPE' => 4,
			'distances' => 0,
			'speed' => Pace::STANDARD,
			'types' => 0,
			'pulse' => 0,
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
		$sports = self::cacheAllSports();

		foreach ($sports as $sport) {
			self::$AllSports[(string)$sport['id']] = $sport;
		}
	}

        /**
         * Cache all sports for user
         */
        static private function cacheAllSports() {
            $sports = Cache::get('sport');
			if (is_null($sports)) {
				$sports = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'sport` '.self::getOrder())->fetchAll();
				Cache::set('sport', $sports, '3600');
			}

            return $sports;
        }

	/**
	 * Get order
	 * @return string
	 */
	static private function getOrder() {
		return Configuration::ActivityForm()->orderSports()->asQuery();
	}

	/**
	 * Reinit all sports
	 *
	 * Use this method after updating sports table
	 */
	static public function reInitAllSports() {
		Cache::delete('sport');

		self::$AllSports = array();
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
		$Sports = DB::getInstance()->query('SELECT sportid, COUNT(sportid) as scount FROM `'.PREFIX.'training` GROUP BY sportid')->fetchAll();
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
