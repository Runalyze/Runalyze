<?php
/**
 * This file contains class::Insrter
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Insert object to database
 * 
 * It may be of need to set an object before using prepared statements.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class Inserter {
	/**
	 * PDO
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * Object
	 * @var \Runalyze\Model\Entity
	 */
	protected $Object;

	/**
	 * Prepared insert statement
	 * @var \PDOStatement
	 */
	protected $Prepared = null;

	/**
	 * @var int
	 */
	protected $InsertedID = false;

	/**
	 * Construct inserter
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
	 * Keys to insert
	 * @return array
	 */
	abstract protected function keys();

	/**
	 * Prepare insert
	 * @throws \RuntimeException
	 */
	public function prepare() {
		$keys = $this->keys();

		if (empty($keys)) {
			throw new \RuntimeException('This class does not support prepared inserts.');
		}

		$this->Prepared = $this->PDO->prepare('
			INSERT INTO `'.PREFIX.$this->table().'`
			(`'.implode('`,`', $keys).'`)
			VALUES (:'.implode(', :', $keys).')
		');
	}

	/**
	 * Set object
	 * @param \Runalyze\Model\Entity $object
	 */
	final public function insert(Entity $object = null) {
		if (!is_null($object)) {
			$this->Object = $object;
		}

		$this->before();
		$this->runInsert();
		$this->after();
	}

	/**
	 * Run insert
	 */
	private function runInsert() {
		if (!is_null($this->Prepared)) {
			$this->runPreparedInsert();
		} else {
			$this->runManualInsert();
		}

		$this->InsertedID = $this->PDO->lastInsertId();

		if ($this->Object instanceof EntityWithID) {
			$this->Object->setID($this->InsertedID);
		}
	}

	/**
	 * Run prepared statement
	 */
	private function runPreparedInsert() {
		$values = array();

		foreach ($this->keys() as $key) {
			$values[':'.$key] = $this->value($key);
		}

		$this->Prepared->execute($values);
	}

	/**
	 * Run manual insert
	 */
	private function runManualInsert() {
		$keys = $this->keys();
		$values = array();

		foreach ($keys as $key) {
			$value = $this->value($key);
			$values[] = is_null($value) ? 'NULL' : $this->PDO->quote($value);
		}

		$this->PDO->exec('
			INSERT INTO `'.PREFIX.$this->table().'`
			(`'.implode('`,`', $keys).'`)
			VALUES ('.implode(',', $values).')
		');
	}

	/**
	 * Value for key
	 * @param string $key
	 * @return string
	 */
	protected function value($key) {
		$value = $this->Object->get($key);

		if (is_array($value)) {
			return Entity::implode($value);
		}

		return $value;
	}

	/**
	 * Tasks before insertion
	 */
	protected function before() {
		$this->Object->synchronize();
	}

	/**
	 * Tasks after insertion
	 */
	protected function after() {}

	/**
	 * Last inserted ID
	 * @return int
	 * @throws \RuntimeException
	 */
	final public function insertedID() {
		if (!($this->Object instanceof EntityWithID)) {
			throw new \RuntimeException('Only objects with id serve an inserted id.');
		}

		return $this->InsertedID;
	}
}