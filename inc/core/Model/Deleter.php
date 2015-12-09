<?php
/**
 * This file contains class::Deleter
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Delete object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class Deleter {
	/**
	 * PDO
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * Old object
	 * @var \Runalyze\Model\EntityWithID
	 */
	protected $Object;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		$this->PDO = $connection;
		$this->Object = $object;
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
	abstract protected function where();

	/**
	 * Delete object
	 * @param \Runalyze\Model\Entity $oldObject [optional]
	 * @throws \RuntimeException
	 */
	final public function delete(Object $oldObject = null) {
		if (!is_null($oldObject)) {
			$this->Object = $oldObject;
		}

		if (is_null($this->Object)) {
			throw new \RuntimeException('The deleter does not have any object to delete.');
		}

		$this->runDelete();
	}

	/**
	 * Run delete
	 */
	protected function runDelete() {
		$this->before();

		$this->PDO->exec(
			'DELETE FROM `'.PREFIX.$this->table().'` '.
			'WHERE '.$this->where()
		);

		$this->after();
	}

	/**
	 * Tasks before delete
	 */
	protected function before() {}

	/**
	 * Tasks after delete
	 */
	protected function after() {}
}