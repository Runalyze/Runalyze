<?php
/**
 * This file contains class::DataObject
 * @package Runalyze\DataObjects
 */
/**
 * Object for data from database
 * 
 * A DataObject represents a row from database.
 * Each subclass needs its own DatabaseScheme.
 * All columns (and fields/fieldsets) are defined there.
 * 
 * A DataObject has standard get()- and set()-methods for all properties.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects
 */
abstract class DataObject {
	/**
	 * Enum as argument for constructor: get last object
	 * @var string 
	 */
	static public $LAST_OBJECT = 'LAST';

	/**
	 * Default id for empty object
	 * @var int 
	 */
	static public $DEFAULT_ID = -1;

	/**
	 * Internal ID of this dataset in database
	 * @var int
	 */
	private $id;

	/**
	 * Internal array from database
	 * @var array
	 */
	private $data = array();

	/**
	 * DatabaseScheme
	 * @var DatabaseScheme 
	 */
	protected $DatabaseScheme = null;

	/**
	 * Subclass must init $tableName and $schemeFile 
	 */
	abstract protected function initDatabaseScheme();

	/**
	 * Constructor
	 * @param mixed $idOrArrayOrKey id | array from database | 'LAST'
	 */
	final public function __construct($idOrArrayOrKey) {
		$this->initDatabaseScheme();

		if ($idOrArrayOrKey == self::$DEFAULT_ID) {
			$this->constructAsDefaultObject();
			return;
		}

		$this->tryToSetDataFrom($idOrArrayOrKey);
		$this->checkForCorrectData();
	}

	/**
	 * Try to set data
	 * @param mixed $idOrArrayOrKey id | array from database | 'LAST'
	 */
	private function tryToSetDataFrom($idOrArrayOrKey) {
		if (is_array($idOrArrayOrKey) && isset($idOrArrayOrKey['id']))
			$this->data = $idOrArrayOrKey;
		elseif (is_numeric($idOrArrayOrKey))
			$this->data = Mysql::getInstance()->fetch($this->tableName(), $idOrArrayOrKey);
		elseif ($idOrArrayOrKey === self::$LAST_OBJECT)
			$this->loadLastObject();
	}

	/**
	 * Load last object
	 */
	private function loadLastObject() {
		if ($this->DatabaseScheme->hasTimestamp())
			$this->data = Mysql::getInstance()->fetchSingle('SELECT * FROM '.$this->tableName().' ORDER BY time DESC');
		else
			$this->data = Mysql::getInstance()->fetch($this->tableName(), 'LAST');

		if (empty($this->data))
			$this->constructAsDefaultObject();
	}

	/**
	 * Create object default array for internal data from scheme
	 */
	private function constructAsDefaultObject() {
		$this->id   = self::$DEFAULT_ID;
		$this->data = $this->databaseScheme()->getDefaultArray();
		$this->data['id'] = $this->id;

		$this->fillDefaultObject();
	}

	/**
	 * Fill default object with values
	 * 
	 * This function can be implemented in the subclass.
	 * With this function, complex values can be set for the default object.
	 */
	protected function fillDefaultObject() {}

	/**
	 * Check internal data or raise error 
	 */
	private function checkForCorrectData() {
		if (empty($this->data)) {
			Error::getInstance()->addError('Cannot construct DataObject, id or array not given correctly');
			$this->constructAsDefaultObject();
		} else
			$this->id = $this->data['id'];
	}

	/**
	 * Get id
	 * @return int 
	 */
	final public function id() {
		return $this->id;
	}

	/**
	 * Get tablename
	 * @return string
	 */
	final protected function tableName() {
		return $this->DatabaseScheme->tableName();
	}

	/**
	 * Get DatabaseScheme
	 * @return DatabaseScheme 
	 */
	final public function databaseScheme() {
		return $this->DatabaseScheme;
	}

	/**
	 * Get DatabaseScheme
	 * @return &DatabaseScheme 
	 */
	final public function &databaseSchemeReference() {
		return $this->DatabaseScheme;
	}

	/**
	 * Get a value
	 * @param string $propertyName
	 * @return mixed
	 */
	final protected function get($propertyName) {
		if (!isset($this->data[$propertyName]))
			Error::getInstance()->addWarning('DataObject: tried to get unknown property "'.$propertyName.'"');
		else
			return $this->data[$propertyName];
	}

	/**
	 * Get complete array
	 * @return array
	 */
	final public function getArray() {
		return $this->data;
	}

	/**
	 * Set a given value
	 * 
	 * To avoid properties being set directly, use isAllowedToSet($propertyName) in subclass
	 * @param string $propertyName
	 * @param mixed $value
	 */
	final protected function set($propertyName, $value) {
		if (!array_key_exists($propertyName, $this->data))
			Error::getInstance()->addWarning('DataObject: tried to set unknown property "'.$propertyName.'"');
		elseif ($this->isAllowedToSet($propertyName))
			$this->data[$propertyName] = $value;
		else
			Error::getInstance()->addWarning('DataObject: setting "'.$propertyName.'" is not allowed.');
	}

	/**
	 * Set all data from array
	 * @param array $Array
	 */
	final public function setFromArray($Array) {
		foreach ($Array as $key => $value)
			if (array_key_exists($key, $this->data) && $this->isAllowedToSet($key))
				$this->data[$key] = $value;
	}

	/**
	 * Is it allowed to set this property?
	 * 
	 * Should be overwritten by subclass
	 * @param string $propertyName
	 */
	protected function isAllowedToSet($propertyName) {
		switch ($propertyName) {
			case 'id':
				return false;
			default:
				return true;
		}
	}

	/**
	 * Set all internal values as post data
	 */
	final public function setValuesAsPostData() {
		$_POST = array_merge($_POST, $this->data);
	}

	/**
	 * Tasks to perform before insert
	 */
	protected function tasksBeforeInsert() {}

	/**
	 * Tasks to perform after insert
	 */
	protected function tasksAfterInsert() {}

	/**
	 * Tasks to perform before update
	 */
	protected function tasksBeforeUpdate() {}

	/**
	 * Tasks to perform after update
	 */
	protected function tasksAfterUpdate() {}

	/**
	 * Insert object to database
	 */
	final public function insert() {
		$this->tasksBeforeInsert();
		$this->insertToDatabase();
		$this->tasksAfterInsert();
	}

	/**
	 * Insert to database
	 */
	private function insertToDatabase() {
		$dataCopy = $this->data;
		unset($dataCopy['id']);

		$this->id = Mysql::getInstance()->insert($this->tableName(), array_keys($dataCopy), array_values($dataCopy));
	}

	/**
	 * Update object in database
	 */
	final public function update() {
		$this->tasksBeforeUpdate();
		$this->updateDatabase();
		$this->tasksAfterUpdate();
	}

	/**
	 * Update database
	 */
	private function updateDatabase() {
		$columns = array_keys($this->data);
		$values  = array_values($this->data);

		Mysql::getInstance()->update($this->tableName(), $this->id, $columns, $values);
	}
}