<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\EquipmentType
 */

namespace Runalyze\Model\EquipmentType;

use Runalyze\Model;

/**
 * Insert equipment type to database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\EquipmentType
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * Object
	 * @var \Runalyze\Model\EquipmentType\Entity
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\EquipmentType\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
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
			Entity::allDatabaseProperties()
		);
	}
}