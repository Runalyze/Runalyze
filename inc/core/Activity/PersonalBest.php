<?php
/**
 * This file contains class::PersonalBest
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Configuration;

use PDO;
use DB;

/**
 * Personal Bests
 *
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class PersonalBest {
	/**
	 * @var \PDO;
	 */
	protected $PDO;

	/**
	 * @var float
	 */
	protected $Distance;
	
	/**
	 * @var int
	 */
	protected $SportId;

	/**
	 * @var mixed
	 */
	protected $Time = false;

	/**
	 * @var mixed
	 */
	protected $ActivityID = null;

	/**
	 * @var mixed
	 */
	protected $Timestamp = null;

	/**
	 * @var array distance => pb
	 */
	public static $PBs = array();

	/**
	 * @var boolean
	 */
	public static $USE_STATIC_CACHE = true;

	/**
	 * Activate use of static cache
	 */
	public static function activateStaticCache() {
		self::$USE_STATIC_CACHE = true;
	}

	/**
	 * Deactivate use of static cache
	 */
	public static function deactivateStaticCache() {
		self::$USE_STATIC_CACHE = false;
		self::$PBs = array();
	}

	/**
	 * Prefetch multiple PBs
	 * 
	 * This method should be used to avoid multiple requests.
	 * It requires the usage of the static cache.
	 * 
	 * Usage:
	 * <pre>PersonalBest::activateStaticCache();
	 * PersonalBest::lookupDistances(array(3, 5, 10), 1, $PDO);
	 * new PersonalBest(3);
	 * ...</pre>
	 * 
	 * @param array $distances distances in [km]
	 * @param int $sportid sportid 
	 * @param \PDO $pdo [optional]
	 * @param boolean $withDetails [optional]
	 * @return int number of fetches PBs
	 */
	public static function lookupDistances(array $distances, $sportid, PDO $pdo = null, $withDetails = false) {
		foreach ($distances as $km) {
			self::$PBs[(float)$km] = false;
		}

		$PDO = is_null($pdo) ? DB::getInstance() : $pdo;
		$Data = $PDO->query(self::groupedQuery($distances, $sportid, $withDetails))->fetchAll();

		foreach ($Data as $result) {
			self::$PBs[(float)$result['official_distance']] = $withDetails ? $result : $result['pb'];
		}

		return count($Data);
	}

	/**
	 * Query to lookup distances
	 * @param array $distances distances in [km]
	 * @param int $sportid Sportid for lookup
	 * @param boolean $withDetails [optional]
	 * @return string
	 */
	private static function groupedQuery(array $distances, $sportid, $withDetails = false) {
		$distances = array_filter($distances, 'is_numeric');

		if ($withDetails) {
			return 'SELECT r.activity_id, tr.time, r.`official_distance`, MIN(r.`official_time`) as `pb` FROM `'.PREFIX.'raceresult` as r '.
				'LEFT JOIN `'.PREFIX.'training` as tr ON tr.`id`= r.`activity_id` '.
				'WHERE r.`accountid`='.\SessionAccountHandler::getId().' '.
				'AND r.`official_distance` IN('.implode(',', $distances).') '.
				'AND tr.`sportid`='.$sportid.' '.
				'GROUP BY r.`official_distance`';
		}

		return 'SELECT r.activity_id, r.`official_distance`, MIN(r.`official_time`) as `pb` FROM `'.PREFIX.'raceresult` as r '.
				'LEFT JOIN `'.PREFIX.'training` as tr ON tr.`id`= r.`activity_id` '.
				'WHERE r.`accountid`='.\SessionAccountHandler::getId().' '.
				'AND r.`official_distance` IN('.implode(',', $distances).') '.
				'AND tr.`sportid`='.$sportid.' '.
				'GROUP BY r.`official_distance`';
	}

	/**
	 * @param float $distance [km]
	 * @param int $sportid Sportid for lookup
	 * @param \PDO $pdo [optional]
	 * @param boolean $autoLookup [optional]
	 * @param boolean $withDetails [optional]
	 */
	public function __construct($distance, $sportid, PDO $pdo = null, $autoLookup = true, $withDetails = false) {
		$this->Distance = $distance;
		$this->PDO = is_null($pdo) ? DB::getInstance() : $pdo;
		$this->SportId = $sportid;

		if ($autoLookup) {
			if ($withDetails) {
				$this->lookupWithDetails();
			} else {
				$this->lookup();
			}
		}
	}

	/**
	 * @return \Runalyze\Activity\PersonalBest this-reference
	 */
	public function lookup() {
		$this->ActivityID = null;
		$this->Timestamp = null;

		if (self::$USE_STATIC_CACHE && isset(self::$PBs[(float)$this->Distance])) {
			if (is_array(self::$PBs[(float)$this->Distance])) {
				$this->Time = self::$PBs[(float)$this->Distance]['pb'];
			} else {
				$this->Time = self::$PBs[(float)$this->Distance];
			}
		} else {
			$this->Time = $this->PDO->query(
				'SELECT MIN(r.`official_time`), r.`activity_id` FROM `'.PREFIX.'raceresult` as r '.
				'LEFT JOIN `'.PREFIX.'training` as tr ON tr.`id`= r.`activity_id`'.
				'WHERE r.`accountid`='.\SessionAccountHandler::getId().'  '. 
				'AND r.`official_distance`="'.$this->Distance.'"'.
				'AND tr.`sportid`='.$this->SportId.''
			)->fetchColumn();

			if ($this->Time == null) {
				$this->Time = false;
			} elseif (self::$USE_STATIC_CACHE) {
				self::$PBs[(float)$this->Distance] = $this->Time;
			}
		}

		return $this;
	}

	/**
	 * @return \Runalyze\Activity\PersonalBest this-reference
	 */
	public function lookupWithDetails() {
		if (self::$USE_STATIC_CACHE && isset(self::$PBs[(float)$this->Distance]) && is_array(self::$PBs[(float)$this->Distance])) {
			$Data = self::$PBs[(float)$this->Distance];
		} else {
			$Data = $this->PDO->query(
				'SELECT r.`activity_id`, r.`official_time` as pb, tr.`time` FROM `'.PREFIX.'raceresult` as r '.
				'LEFT JOIN `'.PREFIX.'training` as tr ON tr.`id` = r.`activity_id` '.
				'WHERE r.`accountid`='.\SessionAccountHandler::getId().'  '.
				'AND r.`official_distance`="'.$this->Distance.'" '.
				'AND tr.`sportid`='.$this->SportId.' '.
				'ORDER BY r.`official_time` ASC LIMIT 1'
			)->fetch();
		}

		if (!empty($Data)) {
			$this->ActivityID = $Data['activity_id'];
			$this->Timestamp = $Data['time'];
			$this->Time = $Data['pb'];

			if (self::$USE_STATIC_CACHE) {
				self::$PBs[(float)$this->Distance] = $Data;
			}
		} else {
			$this->ActivityID = null;
			$this->Timestamp = null;
			$this->Time = false;
		}

		return $this;
	}

	/**
	 * Does a personal best exist for this distance?
	 * @return boolean
	 */
	public function exists() {
		return (false !== $this->Time);
	}

	/**
	 * Personal best in seconds
	 * @return mixed may be false
	 */
	public function seconds() {
		return $this->Time;
	}

	/**
	 * @return boolean
	 */
	public function knowsActivity() {
		return (null !== $this->ActivityID);
	}

	/**
	 * @return mixed may be null
	 */
	public function activityId() {
		return $this->ActivityID;
	}

	/**
	 * @return mixed may be null
	 */
	public function timestamp() {
		return $this->Timestamp;
	}
}
