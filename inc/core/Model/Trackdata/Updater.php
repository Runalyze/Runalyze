<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;

use Runalyze\Model;

use Cache;

/**
 * Update trackdata in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Trackdata
 */
class Updater extends Model\UpdaterWithAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Trackdata\Entity $newObject [optional]
	 * @param \Runalyze\Model\Trackdata\Entity $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Entity $newObject = null, Entity $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'trackdata';
	}

	/**
	 * Where clause
	 * @return string
	 */
	protected function whereSubclass() {
		return '`activityid`="'.$this->NewObject->activityID().'"';
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

	/**
	 * Tasks after insertion
	 */
	protected function after() {
		parent::after();

		if (Cache::is('trackdata'.$this->NewObject->activityID())) {
			Cache::delete('trackdata'.$this->NewObject->activityID());
		}
	}
}