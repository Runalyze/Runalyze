<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\Sport
 */

namespace Runalyze\Model\Sport;

use Runalyze\Model;

/**
 * Insert sport to database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Sport
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * Object
	 * @var \Runalyze\Model\Sport\Entity
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Sport\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
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
			Entity::allDatabaseProperties()
		);
	}
}