<?php
/**
 * Abstract class for a DataObject, representing data from database
 * @author Hannes Christiansen
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
	 * Constructor for DataObject, loading data from database
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
	 * Desctrucor
	 */
	final public function __destruct() {}

	/**
	 * Create object default array for internal data from scheme
	 */
	private function constructAsDefaultObject() {
		$this->id   = self::$DEFAULT_ID;
		$this->data = $this->databaseScheme()->getDefaultArray();
		$this->data['id'] = $this->id;
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
		elseif ($idOrArrayOrKey === self::$LAST_OBJECT) {
			if ($this->DatabaseScheme->hasTimestamp())
				$this->data = Mysql::getInstance()->fetchSingle('SELECT * FROM '.$this->tableName().' ORDER BY time DESC');
			else
				$this->data = Mysql::getInstance()->fetch($this->tableName, 'LAST');
	
			if (empty($this->data))
				$this->constructAsDefaultObject();
		}
	}

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
	 * Set a given value, can be avoided with isAllowedToSet($propertyName)
	 * @param string $propertyName
	 * @param mixed $value
	 */
	final protected function set($propertyName, $value) {
		if (!isset($this->data[$propertyName]))
			Error::getInstance()->addWarning('DataObject: tried to set unknown property "'.$propertyName.'"');
		elseif ($this->isAllowedToSet($propertyName))
			$this->data[$propertyName] = $value;
		else
			Error::getInstance()->addWarning('DataObject: setting "'.$propertyName.'" is not allowed.');
	}

	/**
	 * Is it allowed to set this property? Should be overwritten by subclass
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
	 * Set all internal values as post data for StandardFormular 
	 */
	final public function setValuesAsPostData() {
		$_POST = array_merge($_POST, $this->data);
	}

	/**
	 * Subclasses can set special values as post data, not representing data from database 
	 */
	protected function setSpecialValuesAsPostData() {}

	/**
	 * Update all set values to database
	 */
	final public function updateFromSetValues() {
		$columns = array_keys($this->data);
		$values  = array_values($this->data);

		Mysql::getInstance()->update($this->tableName(), $this->id, $columns, $values);
	}
}
?>