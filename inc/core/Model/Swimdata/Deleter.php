<?php
/**
 * This file contains class::Deleter
 * @package Runalyze\Model\Swimdata
 */

namespace Runalyze\Model\Swimdata;

use Cache;
use Runalyze\Model\DeleterWithAccountID;

/**
 * Delete object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Swimdata
 */
class Deleter extends DeleterWithAccountID {
	/**
	 * Construct deleter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Swimdata\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'swimdata';
	}

	/**
	 * Where clause
	 * @return string
	 */
	protected function where() {
		return '`activityid`='.$this->Object->get(Entity::ACTIVITYID).' AND '.parent::where();
	}

	/**
	 * Tasks before delete
	 */
	protected function before() {
		parent::before();

		if (!$this->Object->get(Entity::ACTIVITYID)) {
			throw new \RuntimeException('Provided object does not have any activityid.');
		}

		if (Cache::is($this->table().$this->Object->get(Entity::ACTIVITYID))) {
			Cache::delete($this->table().$this->Object->get(Entity::ACTIVITYID));
		}
	}
}