<?php
/**
 * This file contains class::DeleterWithIDAndAccountID
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Delete object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class DeleterWithIDAndAccountID {
	/**
	 * PDO
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * Old object
	 * @var \Runalyze\Model\ObjectWithID
	 */
	protected $Object;

	/**
	 * Account id
	 * @var int
	 */
	protected $AccountID = null;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\ObjectWithID $object [optional]
	 */
	public function __construct(\PDO $connection, ObjectWithID $object = null) {
		$this->PDO = $connection;
		$this->Object = $object;
	}

	/**
	 * Set account id
	 * @param int $id
	 */
	public function setAccountID($id) {
		$this->AccountID = $id;
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	abstract protected function table();

	/**
	 * Where clause
	 * @return string
	 */
	protected function where() {
		return '`id`='.$this->Object->id().' AND `accountid`='.$this->AccountID;
	}

	/**
	 * Update
	 * @param \Runalyze\Model\ObjectWithID $oldObject [optional]
	 * @throws \RuntimeException
	 */
	final public function delete(ObjectWithID $oldObject = null) {
		if (!is_null($oldObject)) {
			$this->Object = $oldObject;
		}

		if (is_null($this->AccountID)) {
			throw new \RuntimeException('Account id must be set.');
		}

		if (is_null($this->Object)) {
			throw new \RuntimeException('The deleter does not have any object to delete.');
		}

		if (!$this->Object->hasID()) {
			throw new \RuntimeException('Provided object does not have any id.');
		}

		$this->before();
		$this->runDelete();
		$this->after();
	}

	/**
	 * Run delete
	 */
	private function runDelete() {
		$this->PDO->exec(
			'DELETE FROM `'.PREFIX.$this->table().'` '.
			'WHERE '.$this->where()
		);
	}

	/**
	 * Tasks before delete
	 */
	protected function before() {
		$this->Object->synchronize();
	}

	/**
	 * Tasks after delete
	 */
	protected function after() {}
}