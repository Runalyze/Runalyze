<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\Swimdata
 */

namespace Runalyze\Model\Swimdata;

use Runalyze\Model;

/**
 * Insert swimdata to database
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Swimdata
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * Object
	 * @var \Runalyze\Model\Swimdata\Entity
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Swimdata\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'swimdata';
	}

	/**
	 * Keys to insert
	 * @return array
	 */
	protected function keys() {
		return array_merge(array(
				self::ACCOUNTID
			),
			array_diff(
				Entity::allProperties(),
				array(
					Entity::SWOLF,
					Entity::SWOLFCYCLES
				)
			)
		);
	}
}