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
	static $PBs = array();

	/**
	 * @var boolean
	 */
	static $USE_STATIC_CACHE = true;

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
	 * @param float $distance [km]
	 * @param \PDO $pdo [optional]
	 * @param boolean $autoLookup [optional]
	 */
	public function __construct($distance, PDO $pdo = null, $autoLookup = true) {
		$this->Distance = $distance;
		$this->PDO = is_null($pdo) ? DB::getInstance() : $pdo;

		if ($autoLookup) {
			$this->lookup();
		}
	}

	/**
	 * @return \Runalyze\Activity\PersonalBest this-reference
	 */
	public function lookup() {
		$this->ActivityID = NULL;
		$this->Timestamp = NULL;

		if (self::$USE_STATIC_CACHE && isset(self::$PBs[$this->Distance])) {
			$this->Time = self::$PBs[$this->Distance];
		} else {
			$this->Time = $this->PDO->query(
				'SELECT MIN(`s`) FROM `'.PREFIX.'training` '.
				'WHERE `typeid`="'.Configuration::General()->competitionType().'" '.
				'AND `distance`="'.$this->Distance.'"'
			)->fetchColumn();

			if ($this->Time == NULL) {
				$this->Time = false;
			} elseif (self::$USE_STATIC_CACHE) {
				self::$PBs[$this->Distance] = $this->Time;
			}
		}

		return $this;
	}

	/**
	 * @return \Runalyze\Activity\PersonalBest this-reference
	 */
	public function lookupWithDetails() {
		$Data = $this->PDO->query(
			'SELECT `id`, `s`, `time` FROM `'.PREFIX.'training` '.
			'WHERE `typeid`="'.Configuration::General()->competitionType().'" '.
			'AND `distance`="'.$this->Distance.'" '.
			'ORDER BY `s` ASC LIMIT 1'
		)->fetch();

		if (!empty($Data)) {
			$this->ActivityID = $Data['id'];
			$this->Timestamp = $Data['time'];
			$this->Time = $Data['s'];
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