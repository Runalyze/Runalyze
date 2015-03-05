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
 * Different pace types/units
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
	static public $PBs = array();

	/**
	 * @var boolean
	 */
	static public $USE_STATIC_CACHE = true;

	/**
	 * Activate use of static cache
	 */
	static public function activateStaticCache() {
		self::$USE_STATIC_CACHE = true;
	}

	/**
	 * Deactivate use of static cache
	 */
	static public function deactivateStaticCache() {
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
	 * PersonalBest::lookupDistances(array(3, 5, 10), $PDO);
	 * new PersonalBest(3);
	 * ...</pre>
	 * 
	 * @param array $distances distances in [km]
	 * @param \PDO $pdo [optional]
	 * @param boolean $withDetails [optional]
	 * @return int number of fetches PBs
	 */
	static public function lookupDistances(array $distances, PDO $pdo = null, $withDetails = false) {
		foreach ($distances as $km) {
			self::$PBs[(float)$km] = false;
		}

		$PDO = is_null($pdo) ? DB::getInstance() : $pdo;

		$Data = $PDO->query(self::groupedQuery($distances, $withDetails))->fetchAll();

		foreach ($Data as $result) {
			self::$PBs[(float)$result['distance']] = $withDetails ? $result : $result['pb'];
		}

		return count($Data);
	}

	/**
	 * Query to lookup distances
	 * @param array $distances distances in [km]
	 * @param boolean $withDetails [optional]
	 * @return string
	 */
	static private function groupedQuery(array $distances, $withDetails = false) {
		$distances = array_filter($distances, 'is_numeric');

		if ($withDetails) {
			// This query is damn slow. Don't use it so far.
			return 'SELECT * FROM ('.
						'SELECT `id`, `distance`, `s`, `time`, ('.
							'SELECT COUNT(*) FROM `'.PREFIX.'training` rt1 '.
							'WHERE `rt1`.`distance` = `rt`.`distance` AND '.
								'(`rt1`.`s` < `rt`.`s` OR (`rt1`.`s` = `rt`.`s` AND `rt1`.`time` < `rt`.`time`)) AND '.
							'`accountid`='.\SessionAccountHandler::getId().' AND `typeid`="'.Configuration::General()->competitionType().'" '.
						') `num` FROM `'.PREFIX.'training` `rt` '.
						'WHERE `accountid`='.\SessionAccountHandler::getId().' AND `typeid`="'.Configuration::General()->competitionType().'" AND '.
						'`distance` IN('.implode(',', $distances).')'.
					') AS `tt` WHERE `num`=0';
		}

		return 'SELECT `distance`, MIN(`s`) as `pb` FROM `'.PREFIX.'training` '.
				'WHERE `typeid`="'.Configuration::General()->competitionType().'" '.
				'AND `distance` IN('.implode(',', $distances).') GROUP BY `distance`';
	}

	/**
	 * @param float $distance [km]
	 * @param \PDO $pdo [optional]
	 * @param boolean $autoLookup [optional]
	 * @param boolean $withDetails [optional]
	 */
	public function __construct($distance, PDO $pdo = null, $autoLookup = true, $withDetails = false) {
		$this->Distance = $distance;
		$this->PDO = is_null($pdo) ? DB::getInstance() : $pdo;

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
		$this->ActivityID = NULL;
		$this->Timestamp = NULL;

		if (self::$USE_STATIC_CACHE && isset(self::$PBs[(float)$this->Distance])) {
			if (is_array(self::$PBs[(float)$this->Distance])) {
				$this->Time = self::$PBs[(float)$this->Distance]['s'];
			} else {
				$this->Time = self::$PBs[(float)$this->Distance];
			}
		} else {
			$this->Time = $this->PDO->query(
				'SELECT MIN(`s`) FROM `'.PREFIX.'training` '.
				'WHERE `typeid`="'.Configuration::General()->competitionType().'" '.
				'AND `distance`="'.$this->Distance.'"'
			)->fetchColumn();

			if ($this->Time == NULL) {
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
				'SELECT `id`, `s`, `time` FROM `'.PREFIX.'training` '.
				'WHERE `typeid`="'.Configuration::General()->competitionType().'" '.
				'AND `distance`="'.$this->Distance.'" '.
				'ORDER BY `s` ASC LIMIT 1'
			)->fetch();
		}

		if (!empty($Data)) {
			$this->ActivityID = $Data['id'];
			$this->Timestamp = $Data['time'];
			$this->Time = $Data['s'];

			if (self::$USE_STATIC_CACHE) {
				self::$PBs[(float)$this->Distance] = $Data;
			}
		} else {
			$this->ActivityID = NULL;
			$this->Timestamp = NULL;
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
		return (NULL !== $this->ActivityID);
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