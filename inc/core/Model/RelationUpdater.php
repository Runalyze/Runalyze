<?php
/**
 * This file contains class::RelationUpdater
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Update a many-to-many relation in database
 * 
 * In particular, this updater thinks of a table (`self_column`, `other_column`)
 * and requires the id of `self_column`. It will update the relations by
 * comparing an array of old values of `other_column` with an array of new
 * values.
 * 
 * If you only want to create relations, keep the old array empty.
 * If you only want to delete relations, keep the new array empty.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class RelationUpdater {
	/**
	 * PDO
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * ID of this object
	 * @var int
	 */
	protected $SelfID;

	/**
	 * Old related IDs
	 * @var array
	 */
	protected $OtherIDsOld = array();

	/**
	 * New related IDs
	 * @var array
	 */
	protected $OtherIDsNew = array();

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param int $selfID
	 * @param array $otherIDsNew [optional]
	 * @param array $otherIDsOld [optional]
	 */
	public function __construct(\PDO $connection, $selfID, array $otherIDsNew = array(), array $otherIDsOld = array()) {
		$this->PDO = $connection;
		$this->SelfID = $selfID;
		$this->OtherIDsNew = $otherIDsNew;
		$this->OtherIDsOld = $otherIDsOld;
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	abstract protected function table();

	/**
	 * Column name for 'this' object
	 * @return string
	 */
	abstract protected function selfColumn();

	/**
	 * Column name for 'related' objects
	 * @return string
	 */
	abstract protected function otherColumn();

	/**
	 * Update
	 * 
	 * If you only want to create relations, keep the old array empty.
	 * If you only want to delete relations, keep the new array empty.
	 * 
	 * @param array $otherIDsNew [optional]
	 * @param array $otherIDsOld [optional]
	 */
	final public function update(array $otherIDsNew = array(), array $otherIDsOld = array()) {
		if (!empty($otherIDsNew)) {
			$this->OtherIDsNew = $otherIDsNew;
		}

		if (!empty($otherIDsOld)) {
			$this->OtherIDsOld = $otherIDsOld;
		}

		$this->beforeUpdate();
		$this->removeOldRelatives();
		$this->addNewRelatives();
		$this->afterUpdate();
	}

	/**
	 * Tasks to run before update
	 */
	protected function beforeUpdate() {}

	/**
	 * Tasks to run after update
	 */
	protected function afterUpdate() {}

	/**
	 * Add new relatives
	 */
	private function removeOldRelatives() {
		$removedRelatives = array_diff($this->OtherIDsOld, $this->OtherIDsNew);
		$Delete = $this->PDO->prepare('DELETE FROM `'.PREFIX.$this->table().'` WHERE `'.$this->selfColumn().'`=? AND `'.$this->otherColumn().'`=?');

		foreach ($removedRelatives as $id) {
			$Delete->execute(array($this->SelfID, $id));
		}
	}

	/**
	 * Add new relatives
	 */
	private function addNewRelatives() {
		$addedRelatives = array_diff($this->OtherIDsNew, $this->OtherIDsOld);
		$Insert = $this->PDO->prepare('INSERT INTO `'.PREFIX.$this->table().'` (`'.$this->selfColumn().'`, `'.$this->otherColumn().'`) VALUES(?, ?)');

		foreach ($addedRelatives as $id) {
			$Insert->execute(array($this->SelfID, $id));
		}
	}
}