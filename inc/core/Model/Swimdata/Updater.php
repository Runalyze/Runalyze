<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Swimdata
 */

namespace Runalyze\Model\Swimdata;

use Runalyze\Model;

use Cache;

/**
 * Update swimdata in database
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Swimdata
 */
class Updater extends Model\UpdaterWithAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Swimdata\Entity
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Swimdata\Entity
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Swimdata\Entity $newObject [optional]
	 * @param \Runalyze\Model\Swimdata\Entity $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Entity $newObject = null, Entity $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'swimdata';
	}

	/**
	 * Where clause
	 * @return string
	 */
	protected function whereSubclass() {
		return '`activityid`="'.$this->NewObject->activityID().'"';
	}

	/**
	 * Keys to update
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

	/**
	 * Tasks after insertion
	 */
	protected function after() {
		parent::after();

		if (Cache::is('swimdata'.$this->NewObject->activityID())) {
			Cache::delete('swimdata'.$this->NewObject->activityID());
		}
	}
}