<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Sport
 */

namespace Runalyze\Model\Sport;

use Runalyze\Model;

/**
 * Update sport in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Sport
 */
class Updater extends Model\UpdaterWithIDAndAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Sport\Object
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Sport\Object
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Sport\Object $newObject [optional]
	 * @param \Runalyze\Model\Sport\Object $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Object $newObject = null, Object $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'sport';
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