<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\RaceResult
 */

namespace Runalyze\Model\RaceResult;

use Runalyze\Model;

/**
 * Update RaceResult in database
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Model\RaceResult
 */
class Updater extends Model\UpdaterWithAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\RaceResult\Entity
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\RaceResult\Entity
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\RaceResult\Entity $newObject [optional]
	 * @param \Runalyze\Model\RaceResult\Entity $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Entity $newObject = null, Entity $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'raceresult';
	}
	
	/**
	 * Where clause
	 * @return string
	 */
	 protected function whereSubclass() {
	     return "`activity_id` = '".$this->OldObject->activityId()."'";
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