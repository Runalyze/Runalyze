<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\Equipment
 */

namespace Runalyze\Model\Equipment;

use Runalyze\Model;

/**
 * Insert equipment to database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Equipment
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * Object
	 * @var \Runalyze\Model\Equipment\Entity
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Equipment\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
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
			Entity::allDatabaseProperties()
		);
	}
}