<?php
/**
 * This file contains class::UpdaterWithAccountID
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Update object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class UpdaterWithID extends Updater {
	/**
	 * Old object
	 * @var \Runalyze\Model\ObjectWithID
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\ObjectWithID
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\ObjectWithID $newObject [optional]
	 * @param \Runalyze\Model\ObjectWithID $oldObject [optional]
	 */
	public function __construct(\PDO $connection, ObjectWithID $newObject = null, ObjectWithID $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Where clause
	 * @return string
	 * @throws \RuntimeException
	 */
	final protected function where() {
		if (!$this->NewObject->hasID()) {
			throw new \RuntimeException('Provided object does not have any id.');
		}

		return '`id`='.$this->NewObject->id();
	}
}