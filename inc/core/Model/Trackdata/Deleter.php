<?php
/**
 * This file contains class::Deleter
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;

use Cache;
use Runalyze\Model\DeleterWithAccountID;

/**
 * Delete object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Trackdata
 */
class Deleter extends DeleterWithAccountID {
	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Trackdata\Object $object [optional]
	 */
	public function __construct(\PDO $connection, Object $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'trackdata';
	}

	/**
	 * Where clause
	 * @return string
	 */
	protected function where() {
		return '`activityid`='.$this->Object->get(Object::ACTIVITYID).' AND '.parent::where();
	}

	/**
	 * Tasks before delete
	 */
	protected function before() {
		parent::before();

		if (!$this->Object->get(Object::ACTIVITYID)) {
			throw new \RuntimeException('Provided object does not have any activityid.');
		}

		if (Cache::is($this->table().$this->Object->get(Object::ACTIVITYID))) {
			Cache::delete($this->table().$this->Object->get(Object::ACTIVITYID));
		}
	}
}