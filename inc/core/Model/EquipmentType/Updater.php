<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\EquipmentType
 */

namespace Runalyze\Model\EquipmentType;

use Runalyze\Model;

/**
 * Update equipment type in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\EquipmentType
 */
class Updater extends Model\UpdaterWithIDAndAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\EquipmentType\Object
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\EquipmentType\Object
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\EquipmentType\Object $newObject [optional]
	 * @param \Runalyze\Model\EquipmentType\Object $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Object $newObject = null, Object $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'equipment_type';
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