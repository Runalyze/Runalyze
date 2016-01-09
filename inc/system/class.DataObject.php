<?php
/**
 * This file contains class::DataObject
 * @package Runalyze\DataObjects
 */

use Runalyze\Error;

/**
 * Object for data from database
 * 
 * A DataObject represents a row from database.
 * Each subclass needs its own DatabaseScheme.
 * All columns (and fields/fieldsets) are defined there.
 * 
 * A DataObject has standard (protected!) get()- and set()-methods for all properties.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects
 */
abstract class DataObject {
	/**
	 * Enum as argument for constructor: get last object
	 * @var string 
	 */
	public static $LAST_OBJECT = 'LAST';

	/**
	 * Default id for empty object
	 * @var int 
	 */
	public static $DEFAULT_ID = -1;

	/**
	 * Internal flag: debug insert/update-queries
	 * @var bool
	 */
	private static $DEBUG_QUERIES = false;

	/**
	 * Array seperator for gps-data in database
	 * @var string
	 */
	public static $ARR_SEP = '|';

	/**
	 * Internal ID of this dataset in database
	 * @var int
	 */
	protected $id;

	/**
	 * Internal array from database
	 * @var array
	 */
	protected $data = array();

	/**
	 * Cache for exploded arrays from internal data
	 * @var array
	 */
	private $dataArrayCache = array();

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
	public function __construct($idOrArrayOrKey) {
		$this->initDatabaseScheme();

		if ($idOrArrayOrKey == self::$DEFAULT_ID || is_null($idOrArrayOrKey) || $idOrArrayOrKey === 0 || $idOrArrayOrKey === '0') {
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
			$this->data = DB::getInstance()->fetchByID($this->tableName(), $idOrArrayOrKey);
		elseif ($idOrArrayOrKey === self::$LAST_OBJECT)
			$this->loadLastObject();
	}

	/**
	 * Load last object
	 */
	private function loadLastObject() {
		if ($this->DatabaseScheme->hasTimestamp()) {
			$this->data = DB::getInstance()->query('SELECT * FROM `'.$this->tableName().'` WHERE accountid = '.SessionAccountHandler::getId().' ORDER BY `time` DESC LIMIT 1')->fetch();
                } else {
			$this->data = DB::getInstance()->query('SELECT * FROM `'.$this->tableName().'` WHERE accountid = '.SessionAccountHandler::getId().' ORDER BY `id` DESC LIMIT 1')->fetch();
                }

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
	public function id() {
		return $this->id;
	}

	/**
	 * Is default id?
	 * @return bool
	 */
	public function isDefaultId() {
		return $this->id == self::$DEFAULT_ID;
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
	 * Update single value
	 * @param string $column Column to update
	 * @param mixed $value Value to set
	 */
	final protected function updateValue($column, $value) {
		$this->set($column, $value);

		DB::getInstance()->update($this->tableName(), $this->id(), $column, $value);
	}

	/**
	 * Get a value
	 * @param string $propertyName
	 * @return mixed
	 */
	final public function get($propertyName) {
		if (!array_key_exists($propertyName, $this->data))
			Error::getInstance()->addWarning('DataObject: tried to get unknown property "'.$propertyName.'"');
		else
			return $this->data[$propertyName];

		return '';
	}

	/**
	 * Get complete array
	 * @return array
	 */
	final public function getArray() {
		return $this->data;
	}

	/**
	 * Get array for key
	 * @param string $key
	 * @return array
	 */
	final protected function getArrayFor($key) {
		if (!isset($this->dataArrayCache[$key]))
			$this->dataArrayCache[$key] = explode(self::$ARR_SEP, $this->get($key));

		return $this->dataArrayCache[$key];
	}

	/**
	 * Get first point of array
	 * @param string $key
	 * @return mixed
	 */
	final protected function getFirstArrayPoint($key) {
		$array = $this->getArrayFor($key);

		return reset($array);
	}

	/**
	 * Get last point of array
	 * @param string $key
	 * @return mixed
	 */
	final protected function getLastArrayPoint($key) {
		$array = $this->getArrayFor($key);

		return end($array);
	}

	/**
	 * Set a given value
	 * 
	 * To avoid properties being set directly, use isAllowedToSet($propertyName) in subclass
	 * @param string $propertyName
	 * @param mixed $value
	 */
	final public function set($propertyName, $value) {
		if (!array_key_exists($propertyName, $this->data))
			Error::getInstance()->addWarning('DataObject: tried to set unknown property "'.$propertyName.'"');
		elseif ($this->isAllowedToSet($propertyName))
			$this->data[$propertyName] = $value;
		else
			Error::getInstance()->addWarning('DataObject: setting "'.$propertyName.'" is not allowed.');

		if (array_key_exists($propertyName, $this->dataArrayCache))
			unset($this->dataArrayCache[$propertyName]);
	}

	/**
	 * Force to set a given value
	 * 
	 * WARNING: Only use this method if you know what you are doing!
	 * @param string $propertyName
	 * @param mixed $value
	 */
	final public function forceToSet($propertyName, $value) {
		$this->data[$propertyName] = $value;
	}

	/**
	 * Force to remove a given property
	 * 
	 * WARNING: Only use this method if you know what you are doing!
	 * @param string $propertyName
	 */
	final public function forceToRemove($propertyName) {
		if (isset($this->data[$propertyName]))
			unset($this->data[$propertyName]);
	}

	/**
	 * Has property?
	 * @param string $propertyName
	 * @return boolean
	 */
	final protected function hasProperty($propertyName) {
		return array_key_exists($propertyName, $this->data);
	}

	/**
	 * Set array for key
	 * @param string $key
	 * @param array $array
	 */
	final protected function setArrayFor($key, $array) {
		if (empty($array) || (max($array) == 0 && min($array) == 0))
			return;

		$this->set($key, implode(self::$ARR_SEP, $array));
		$this->dataArrayCache[$key] = $array;
	}

	/**
	 * Clear internal array cache
	 */
	private function clearArrayCache() {
		$this->dataArrayCache = array();
	}

	/**
	 * Set all data from array
	 * @param array $Array
	 */
	final public function setFromArray($Array) {
		foreach ($Array as $key => $value)
			if (array_key_exists($key, $this->data) && $this->isAllowedToSet($key))
				$this->data[$key] = $value;

		$this->clearArrayCache();
	}

	/**
	 * Is it allowed to set this property?
	 * 
	 * Should be overwritten by subclass
	 * @param string $propertyName
	 * @return bool
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
	public function setValuesAsPostData() {
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
	public function insert() {
		$this->tasksBeforeInsert();
		$this->insertToDatabase();
		$this->tasksAfterInsert();
	}

	/**
	 * Insert to database
	 */
	protected function insertToDatabase() {
		$dataCopy = $this->data;
		unset($dataCopy['id']);

		$this->id = DB::getInstance()->insert($this->tableName(), array_keys($dataCopy), array_values($dataCopy));

		if (self::$DEBUG_QUERIES)
			Error::getInstance()->addDebug('Inserted to '.$this->tableName().': '.print_r($dataCopy, true));
	}

	/**
	 * Update object in database
	 */
	public function update() {
		$this->tasksBeforeUpdate();
		$this->updateDatabase();
		$this->tasksAfterUpdate();
	}

	/**
	 * Update database
	 */
	protected function updateDatabase() {
		$columns = array_keys($this->data);
		$values  = array_values($this->data);

		DB::getInstance()->update($this->tableName(), $this->id, $columns, $values);

		if (self::$DEBUG_QUERIES)
			Error::getInstance()->addDebug('Updated #'.$this->id.' '.$this->tableName().': '.print_r($this->data, true));
	}
}