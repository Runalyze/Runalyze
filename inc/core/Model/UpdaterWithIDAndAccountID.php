<?php
/**
 * This file contains class::UpdaterWithIDAndAccountID
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

use Cache;

/**
 * Update object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class UpdaterWithIDAndAccountID extends UpdaterWithAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\ObjectWithID
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\ObjectWithID
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\ObjectWithID $newObject [optional]
	 * @param \Runalyze\Model\ObjectWithID $oldObject [optional]
	 */
	public function __construct(\PDO $connection, ObjectWithID $newObject = null, ObjectWithID $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Where clause
	 * @return string
	 * @throws \RuntimeException
	 */
	final protected function whereSubclass() {
		if (!$this->NewObject->hasID()) {
			throw new \RuntimeException('Provided object does not have any id.');
		}

		return '`id`='.$this->NewObject->id();
	}

	/**
	 * Tasks after insertion
	 */
	protected function after() {
		parent::after();

		if (Cache::is($this->table().$this->NewObject->id())) {
			Cache::delete($this->table().$this->NewObject->id());
		}
	}
}