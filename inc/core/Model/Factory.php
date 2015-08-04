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
	 * Factory
	 * @param int $accountID [optional]
	 */
	public function __construct($accountID = null) {
		$this->DB = DB::getInstance();
		$this->AccountID = $accountID;
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
			\TypeFactory::DataFor($typeid)
			// TODO: The factory must be able to fetch all rows at once
			//$this->arrayByPK('type', $typeid)
		);
	}

	/**
	 * Sport
	 * @param int $sportid
	 * @return \Runalyze\Model\Sport\Object
	 */
	public function sport($sportid) {
		return new Sport\Object(
			\SportFactory::DataFor($sportid)
			// TODO: The factory must be able to fetch all rows at once
			//$this->arrayByPK('sport', $sportid)
		);
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

		// TODO: 
		// provide somehow an internal cache to not read the file every time
		// this needs static properties: ::$CACHE['accountID']['table']['PK']

		$Data = Cache::get($tablename.$id);
		if (is_null($Data)) {
			$Data = $this->fetch($tablename, $id);

			Cache::set($tablename.$id, $Data, $cachetime);
		} else {
			//Cache::touch($tablename.$id);
		}

		return $Data;
	}

	/**
	 * Fetch data
	 * @param string $tablename
	 * @param int $id
	 * @return array
	 */
	protected function fetch($tablename, $id) {
		$field = $this->primaryKey($tablename);
		$AndAccountID = $this->hasAccountID() && $this->tableHasAccountid($tablename) ? 'AND `accountid`='.(int)$this->AccountID : '';

		// TODO:
		// provide a full fetch for tables as sport / type
		$result = $this->DB->query('SELECT * FROM `'.PREFIX.$tablename.'` WHERE `'.$field.'`='.(int)$id.' '.$AndAccountID.' LIMIT 1')->fetch();

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
}