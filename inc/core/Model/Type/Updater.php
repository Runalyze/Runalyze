<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Type
 */

namespace Runalyze\Model\Type;

use Runalyze\Model;

/**
 * Update type in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Type
 */
class Updater extends Model\UpdaterWithIDAndAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Type\Object
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Type\Object
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Type\Object $newObject [optional]
	 * @param \Runalyze\Model\Type\Object $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Object $newObject = null, Object $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'type';
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