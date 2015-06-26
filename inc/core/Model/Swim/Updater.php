<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Swim
 */

namespace Runalyze\Model\Swim;

use Runalyze\Model;

use Cache;

/**
 * Update swimdata in database
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Swim
 */
class Updater extends Model\UpdaterWithAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Swim\Object
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Swim\Object
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Swim\Object $newObject [optional]
	 * @param \Runalyze\Model\Swim\Object $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Object $newObject = null, Object $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'swim';
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
			Object::allProperties()
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