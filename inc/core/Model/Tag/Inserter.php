<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\Tag
 */

namespace Runalyze\Model\Tag;

use Runalyze\Model;

/**
 * Insert tag to database
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Tag
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * Object
	 * @var \Runalyze\Model\Tag\Entity
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Tag\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'tag';
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