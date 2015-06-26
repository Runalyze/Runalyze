<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\Swim
 */

namespace Runalyze\Model\Swim;

use Runalyze\Model;

/**
 * Insert swimdata to database
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Swim
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * Object
	 * @var \Runalyze\Model\Swim\Object
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Swim\Object $object [optional]
	 */
	public function __construct(\PDO $connection, Object $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'swim';
	}

	/**
	 * Keys to insert
	 * @return array
	 */
	protected function keys() {
		return array_merge(array(
				self::ACCOUNTID
			),
			Object::allProperties()
		);
	}

}