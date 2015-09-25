<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Tag
 */

namespace Runalyze\Model\Tag;

use Runalyze\Model;

/**
 * Update tag in database
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Tag
 */
class Updater extends Model\UpdaterWithIDAndAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Tag\Object
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Tag\Object
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Tag\Object $newObject [optional]
	 * @param \Runalyze\Model\Tag\Object $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Object $newObject = null, Object $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
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
			Object::allDatabaseProperties()
		);
	}
}