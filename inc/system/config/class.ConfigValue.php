<?php
/**
 * Class: ConfigValue
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
abstract class ConfigValue {
	/**
	 * Internal array holding all information and values from database
	 * @var array
	 */
	static private $DatabaseValues = array();

	/**
	 * Key
	 * @var string
	 */
	protected $Key = '';

	/**
	 * Value
	 * @var mixed
	 */
	protected $Value = null;

	/**
	 * Options
	 * @var array
	 */
	protected $Options = array(
		'default'	=> '',
		'label'		=> '',
		'tooltip'	=> '',
		'options'	=> array(), // ConfigValueSelect: key => label
		'folder'	=> '', // ConfigValueSelectFile
		'table'		=> '', // ConfigValueSelectDb
		'column'	=> '', // ConfigValueSelectDb
		'onchange'	=> '' // Ajax::$RELOAD_...-flag
		);

	/**
	 * Type - should be overwritten by subclass
	 * @var string
	 */
	protected $type = '';

	/**
	 * Get value from database, return null if not set
	 * @param string $Key
	 * @return mixed
	 */
	static public function getDatabaseValue($Key) {
		if (AccountHandler::$IS_ON_REGISTER_PROCESS)
			self::setDatabaseValues();

		if (empty(self::$DatabaseValues))
			self::setDatabaseValues();

		if (isset(self::$DatabaseValues[$Key]))
			return self::$DatabaseValues[$Key];

		return null;
	}

	/**
	 * Set all consts from database 
	 */
	static private function setDatabaseValues() {
		self::$DatabaseValues = array();

		if (AccountHandler::$IS_ON_REGISTER_PROCESS)
			$ID = AccountHandler::$NEW_REGISTERED_ID;
		else
			$ID = SessionAccountHandler::getId();

		$data = Mysql::getInstance()->fetchAsArray('SELECT `key`,`value` FROM '.PREFIX.'conf WHERE accountid="'.$ID.'"');
		foreach ($data as $confArray)
			self::$DatabaseValues[$confArray['key']] = $confArray['value'];
	}

	/**
	 * Update a value, should primary be used for hidden keys
	 * @param string $KEY
	 * @param mixed $value
	 * @param mixed $accountID
	 */
	static public function update($KEY, $value, $accountID = false) {
		$whereAdd = ($accountID !== false) ? ' AND `accountid`='.$accountID : '';

		Mysql::getInstance()->updateWhere(PREFIX.'conf', '`key`="'.$KEY.'"'.$whereAdd, 'value', $value);
	}

	/**
	 * Construct a new config value
	 * @param string $Key
	 * @param array $Options 
	 */
	public function __construct($Key, $Options = array()) {
		$this->Key = $Key;
		$this->Options = array_merge($this->Options, $Options);

		$this->setValueAndCheckDatabase();
		$this->parsePostData();
		$this->defineConst();
	}

	/**
	 * Parse post data
	 */
	private function parsePostData() {
		$NewValue = null;

		if ($this->type == 'bool') {
			if (isset($_POST[$this->Key.'_sent']))
				$NewValue = isset($_POST[$this->Key]);
		} elseif (isset($_POST[$this->Key])) {
			$NewValue = $_POST[$this->Key];
		}

		if (!is_null($NewValue)) {
			$OldValue = $this->Value;
			$this->setValueFromString($NewValue);

			if ($this->Value != $OldValue) {
				Mysql::getInstance()->updateWhere(PREFIX.'conf', '`key`="'.$this->Key.'"', 'value', $this->getValueAsString());
				$this->setReloadFlag();
			}
		}
	}

	/**
	 * Set value from database or insert to database 
	 */
	private function setValueAndCheckDatabase() {
		$Value = self::getDatabaseValue($this->getKey());

		if (is_null($Value)) {
			$this->setDefaultValue();
			$this->insertToDatabase();
		} else {
			$this->setValueFromString($Value);
		}
	}

	/**
	 * Insert value to database
	 * @return type 
	 */
	private function insertToDatabase() {
		if (FrontendShared::$IS_SHOWN)
			return;

		$columns = array('key', 'value');
		$values  = array($this->getKey(), $this->getValueAsString());

		if (AccountHandler::$IS_ON_REGISTER_PROCESS) {
			$columns[] = 'accountid';
			$values[]  = AccountHandler::$NEW_REGISTERED_ID;
		}

		Mysql::getInstance()->insert(PREFIX.'conf', $columns, $values);
	}

	/**
	 * Set default value from options 
	 */
	private function setDefaultValue() {
		$this->Value = $this->Options['default'];
	}

	/**
	 * Get Key
	 * @return string
	 */
	final public function getKey() {
		return $this->Key;
	}

	/**
	 * Get value
	 * @return mixed
	 */
	final public function getValue() {
		return $this->Value;
	}

	/**
	 * Get value as string, should be overwritten
	 * @return string
	 */
	protected function getValueAsString() {
		return (string)$this->Value;
	}

	/**
	 * Set value from string, should be overwritten
	 * @param string $Value 
	 */
	protected function setValueFromString($Value) {
		$this->Value = (string)$Value;
	}

	/**
	 * Get label for value
	 * @return string
	 */
	final public function getLabel() {
		$Label = !empty($this->Options['label']) ? $this->Options['label'] : $this->Key;

		if (!empty($this->Options['tooltip']))
			$Label = Ajax::tooltip($Label, $this->Options['tooltip']);

		return $Label;
	}

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		return new FormularInput($this->getKey(), $this->getLabel(), $this->getValue());
	}

	/**
	 * Define const
	 */
	private function defineConst() {
		if (!AccountHandler::$IS_ON_REGISTER_PROCESS && !defined('CONF_'.$this->getKey()))
			define('CONF_'.$this->getKey(), $this->getValue());
	}

	/**
	 * Set reload flag (only fire if value has changed!) 
	 */
	final protected function setReloadFlag() {
		if (!empty($this->Options['onchange']))
			Ajax::setReloadFlag($this->Options['onchange']);
	}
}