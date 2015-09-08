<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Equipment
 */

namespace Runalyze\Model\Equipment;

use Runalyze\Model;

/**
 * Update equipment in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Equipment
 */
class Updater extends Model\UpdaterWithIDAndAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Equipment\Object
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Equipment\Object
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Equipment\Object $newObject [optional]
	 * @param \Runalyze\Model\Equipment\Object $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Object $newObject = null, Object $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'equipment';
	}

	/**
	 * Keys to insert
	 * @return array
	 */
	protected function keys() {
		return array_merge(array(
				self::ACCOUNTID
			),
			Object::allDatabaseProperties()
		);
	}
}