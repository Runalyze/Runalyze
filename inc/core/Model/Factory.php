<?php
/**
 * This file contains class::Factory
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

use DB;
use Cache;

/**
 * Model factory
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
class Factory {
	/**
	 * Database
	 * @var \PDOforRunalyze
	 */
	protected $DB;

	/**
	 * Account ID
	 * @var int
	 */
	protected $AccountID;

	/**
	 * Array with tablenames that should be cached statically
	 * @var array
	 */
	protected $UseStaticCacheAndFullFetch = array();

	/**
	 * Static cache to not read file-cache every time
	 * @var array
	 */
	public static $StaticCache = array();

	/**
	 * Factory
	 * @param int $accountID [optional]
	 */
	public function __construct($accountID = null) {
		$this->DB = DB::getInstance();
		$this->AccountID = $accountID;
		$this->UseStaticCacheAndFullFetch = array(
			'sport' => true,
			'type' => true,
			'equipment' => true,
			'equipment_type' => true
		);
	}

	/**
	 * Has account id?
	 * @return bool
	 */
	protected function hasAccountID() {
		return !is_null($this->AccountID);
	}

	/**
	 * Activity
	 * @param int $activityid
	 * @return \Runalyze\Model\Activity\Object
	 */
	public function activity($activityid) {
		return new Activity\Object(
			$this->arrayByPK('training', $activityid)
		);
	}

	/**
	 * Trackdata
	 * @param int $activityid
	 * @return \Runalyze\Model\Trackdata\Object
	 */
	public function trackdata($activityid) {
		return new Trackdata\Object(
			$this->arrayByPK('trackdata', $activityid)
		);
	}
        
	/**
	 * Swimdata
	 * @param int $activityid
	 * @return \Runalyze\Model\Swimdata\Object
	 */
	public function swimdata($activityid) {
		return new Swimdata\Object(
			$this->arrayByPK('swimdata', $activityid)
		);
	}

	/**
	 * Route
	 * @param int $routeid
	 * @return \Runalyze\Model\Route\Object
	 */
	public function route($routeid) {
		return new Route\Object(
			$this->arrayByPK('route', $routeid)
		);
	}

	/**
	 * HRV
	 * @param int $activityid
	 * @return \Runalyze\Model\HRV\Object
	 */
	public function hrv($activityid) {
		return new HRV\Object(
			$this->arrayByPK('hrv', $activityid)
		);
	}

	/**
	 * Type
	 * @param int $typeid
	 * @return \Runalyze\Model\Type\Object
	 */
	public function type($typeid) {
		return new Type\Object(
			$this->arrayByPK('type', $typeid)
		);
	}

	/**
	 * All type objects
	 * @return \Runalyze\Model\Type\Object[]
	 */
	public function allTypes() {
		return $this->allObjects('type', function($data){
			return new Type\Object($data);
		});
	}

	/**
	 * Sport
	 * @param int $sportid
	 * @return \Runalyze\Model\Sport\Object
	 */
	public function sport($sportid) {
		return new Sport\Object(
			$this->arrayByPK('sport', $sportid)
		);
	}

	/**
	 * All sport objects
	 * @return \Runalyze\Model\Sport\Object[]
	 */
	public function allSports() {
		return $this->allObjects('sport', function($data){
			return new Sport\Object($data);
		});
	}

	/**
	 * Equipment type
	 * @param int $equipmentTypeid
	 * @return \Runalyze\Model\EquipmentType\Object
	 */
	public function equipmentType($equipmentTypeid) {
		return new EquipmentType\Object(
			$this->arrayByPK('equipment_type', $equipmentTypeid)
		);
	}

	/**
	 * All equipment type objects
	 * @return \Runalyze\Model\EquipmentType\Object[]
	 */
	public function allEquipmentTypes() {
		return $this->allObjects('equipment_type', function($data){
			return new EquipmentType\Object($data);
		});
	}

	/**
	 * Sport for equipment type
	 * @param int $equipmentTypeid
	 * @param boolean $onlyIDs [optional]
	 * @return int[]|\Runalyze\Model\Sport\Object[]
	 */
	public function sportForEquipmentType($equipmentTypeid, $onlyIDs = false) {
		$Sport = array();

		// TODO: provide a cache for this
		$IDs = $this->DB->query('SELECT `sportid` FROM `'.PREFIX.'equipment_sport` WHERE `equipment_typeid`="'.$equipmentTypeid.'"')->fetchAll(\PDO::FETCH_COLUMN);

		if ($onlyIDs) {
			return $IDs;
		}

		foreach ($IDs as $id) {
			$Sport[] = $this->sport($id);
		}

		return $Sport;
	}

	/**
	 * Equipment types for sport
	 * @param int $sportid
	 * @param boolean $onlyIDs [optional]
	 * @return int[]|\Runalyze\Model\EquipmentType\Object[]
	 */
	public function equipmentTypeForSport($sportid, $onlyIDs = false) {
		$Types = array();

		// TODO: provide a cache for this
		$IDs = $this->DB->query('SELECT `equipment_typeid` FROM `'.PREFIX.'equipment_sport` WHERE `sportid`="'.$sportid.'"')->fetchAll(\PDO::FETCH_COLUMN);

		if ($onlyIDs) {
			return $IDs;
		}

		foreach ($IDs as $id) {
			$Types[] = $this->equipmentType($id);
		}

		return $Types;
	}

	/**
	 * Equipment
	 * @param int $equipmentid
	 * @return \Runalyze\Model\Equipment\Object
	 */
	public function equipment($equipmentid) {
		return new Equipment\Object(
			$this->arrayByPK('equipment', $equipmentid)
		);
	}

	/**
	 * All equipment objects
	 * @return \Runalyze\Model\Equipment\Object[]
	 */
	public function allEquipments() {
		return $this->allObjects('equipment', function($data){
			return new Equipment\Object($data);
		});
	}

	/**
	 * Equipment for activity
	 * @param int $activityid
	 * @param boolean $onlyIDs [optional]
	 * @return int[]|\Runalyze\Model\Equipment\Object[]
	 */
	public function equipmentForActivity($activityid, $onlyIDs = false) {
		$Equipment = array();

		$IDs = $this->DB->query('SELECT `equipmentid` FROM `'.PREFIX.'activity_equipment` WHERE `activityid`="'.$activityid.'"')->fetchAll(\PDO::FETCH_COLUMN);

		if ($onlyIDs) {
			return $IDs;
		}

		foreach ($IDs as $id) {
			$Equipment[] = $this->equipment($id);
		}

		return $Equipment;
	}

	/**
	 * Array by primary key
	 * @param string $tablename
	 * @param int $id
	 * @param int $cachetime [optional]
	 * @return array
	 */
	protected function arrayByPK($tablename, $id, $cachetime = 3600) {
		if (!$cachetime) {
			return $this->fetch($tablename, $id);
		}

		if (isset($this->UseStaticCacheAndFullFetch[$tablename])) {
			return $this->arrayByPKfromStaticCache($tablename, $id);
		}

		return $this->arrayByPKfromCache($tablename, $id, $cachetime);
	}

	/**
	 * Array by primary key from static cache
	 * @param string $tablename
	 * @param int $id
	 * @return array
	 */
	protected function arrayByPKfromStaticCache($tablename, $id) {
		if (!isset(self::$StaticCache[$tablename])) {
			$this->fetchStaticCache($tablename);
		}

		if (isset(self::$StaticCache[$tablename][$id])) {
			return self::$StaticCache[$tablename][$id];
		}

		return array();
	}

	/**
	 * Fetch static cache
	 * @param string $tablename
	 */
	protected function fetchStaticCache($tablename) {
		$pk = $this->primaryKey($tablename);
		$allData = $this->fetch($tablename, 0, true);
		self::$StaticCache[$tablename] = array();

		foreach ($allData as $data) {
			self::$StaticCache[$tablename][$data[$pk]] = $data;
		}
	}

	/**
	 * Array by primary key from cache class
	 * @param string $tablename
	 * @param int $id
	 * @param int $cachetime [optional]
	 * @return array
	 */
	protected function arrayByPKfromCache($tablename, $id, $cachetime) {
		$data = Cache::get($tablename.$id);

		if (is_null($data)) {
			$data = $this->fetch($tablename, $id);

			Cache::set($tablename.$id, $data, $cachetime);
		}

		return $data;
	}

	/**
	 * All data
	 * @param string $tablename
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function allData($tablename) {
		if (!isset($this->UseStaticCacheAndFullFetch[$tablename])) {
			throw new \InvalidArgumentException('The table "'.$tablename.'" does not provide a full fetch');
		}

		if (!isset(self::$StaticCache[$tablename])) {
			$this->fetchStaticCache($tablename);
		}

		return self::$StaticCache[$tablename];
	}

	/**
	 * All objects
	 * @param string $tablename
	 * @param \Closure $constructor
	 * @return \Runalyze\Model\Object[]
	 */
	public function allObjects($tablename, \Closure $constructor) {
		$allObjects = array();
		$allData = $this->allData($tablename);

		foreach ($allData as $data) {
			$allObjects[] = $constructor($data);
		}

		return $allObjects;
	}

	/**
	 * Clear cache
	 * @param string $tablename
	 * @param int $id can be empty if the table uses a full fetch and static cache
	 * @throws \InvalidArgumentException
	 */
	public function clearCache($tablename = false, $id = false) {
		if ($tablename === false) {
			foreach (array_keys($this->UseStaticCacheAndFullFetch) as $tablename) {
				$this->clearCache($tablename);
			}

			return;
		}

		if (isset($this->UseStaticCacheAndFullFetch[$tablename])) {
			if (isset(self::$StaticCache[$tablename])) {
				unset(self::$StaticCache[$tablename]);
			}	
		} elseif ($id) {
			Cache::delete($tablename.$id);
		} else {
			throw new \InvalidArgumentException('Argument $id must be set, the table "'.$tablename.'" does not use a static cache.');
		}
	}

	/**
	 * Fetch data
	 * @param string $tablename
	 * @param int $id
	 * @param boolean $fullFetch
	 * @return array
	 */
	protected function fetch($tablename, $id, $fullFetch = false) {
		$field = $this->primaryKey($tablename);
		$AndAccountID = $this->hasAccountID() && $this->tableHasAccountid($tablename) ? 'AND `accountid`='.(int)$this->AccountID : '';

		if ($fullFetch) {
			$result = $this->DB->query('SELECT * FROM `'.PREFIX.$tablename.'` WHERE 1 '.$AndAccountID.' '.$this->orderBy($tablename))->fetchAll();
		} else {
			$result = $this->DB->query('SELECT * FROM `'.PREFIX.$tablename.'` WHERE `'.$field.'`='.(int)$id.' '.$AndAccountID.' LIMIT 1')->fetch();
		}

		if (!is_array($result)) {
			return array();
		}

		return $result;
	}

	/**
	 * Get primary key
	 * @param string $tablename
	 * @return string
	 */
	protected function primaryKey($tablename) {
		switch ($tablename) {
			case 'trackdata':
			case 'hrv':
				return 'activityid';
			case 'swimdata':
				return 'activityid';
		}

		return 'id';
	}

	/**
	 * Has the table a column 'accountid'?
	 * @param string $tablename
	 * @return boolean
	 */
	protected function tableHasAccountid($tablename) {
		switch ($tablename) {
			case 'account':
			case 'plugin_conf':
				return false;
		}

		return true;
	}

	/**
	 * Order for full fetch
	 * @param string $tablename
	 * @return string
	 */
	protected function orderBy($tablename) {
		switch ($tablename) {
			case 'sport':
				return \Runalyze\Configuration::ActivityForm()->orderSports()->asQuery();
			case 'type':
				return \Runalyze\Configuration::ActivityForm()->orderTypes()->asQuery();
			case 'equipment':
				return \Runalyze\Configuration::ActivityForm()->orderEquipment()->asQuery();
		}

		return '';
	}
}