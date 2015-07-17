<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;

use Runalyze\Model;

/**
 * Insert trackdata to database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Trackdata
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * Object
	 * @var \Runalyze\Model\Trackdata\Object
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Trackdata\Object $object [optional]
	 */
	public function __construct(\PDO $connection, Object $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'trackdata';
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

	// TODO: Calculate power?
}