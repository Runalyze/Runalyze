<?php
/**
 * This file contains class::DeleterWithIDAndAccountID
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

use Cache;

/**
 * Delete object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class DeleterWithIDAndAccountID extends DeleterWithAccountID {
	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\EntityWithID $object [optional]
	 */
	public function __construct(\PDO $connection, EntityWithID $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Where clause
	 * @return string
	 */
	protected function where() {
		return '`id`='.$this->Object->id().' AND '.parent::where();
	}

	/**
	 * Tasks before delete
	 */
	protected function before() {
		parent::before();

		if (!$this->Object->hasID()) {
			throw new \RuntimeException('Provided object does not have any id.');
		}

		if (Cache::is($this->table().$this->Object->id())) {
			Cache::delete($this->table().$this->Object->id());
		}
	}
}