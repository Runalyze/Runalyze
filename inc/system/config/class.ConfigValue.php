<?php
/**
 * This file contains class::ConfigValue
 * @package Runalyze\System\Config
 */
/**
 * Class: ConfigValue
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
abstract class ConfigValue {
	/**
	 * Max length
	 */
	const MAX_LENGTH = 255;

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
		'default'		=> '',
		'label'			=> '',
		'tooltip'		=> '',
		'options'		=> array(), // ConfigValueSelect: key => label
		'folder'		=> '', // ConfigValueSelectFile
		'table'			=> '', // ConfigValueSelectDb
		'column'		=> '', // ConfigValueSelectDb
		'onchange'		=> '', // Ajax::$RELOAD_...-flag
		'onchange_eval'	=> '', // onchange: evaluate code
		'unit'			=> '',
		'size'			=> '',
		'layout'		=> ''
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

		if ($ID == 0 && SharedLinker::isOnSharedPage())
			$ID = SharedLinker::getUserId();

		$data = DB::getInstance()->query('SELECT `key`,`value` FROM '.PREFIX.'conf WHERE accountid="'.(int)$ID.'"')->fetchAll();
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
		if (SharedLinker::isOnSharedPage())
			return;

		$whereAdd = ($accountID !== false) ? ' AND `accountid`='.(int)$accountID : '';

		DB::getInstance()->updateWhere('conf', '`key`='.DB::getInstance()->escape($KEY).$whereAdd, 'value', $value);
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
		//$this->parsePostData();
		$this->defineConst();
		$this->subclassSetup();
	}

	/**
	 * Init function for subclass - can be overwritten in subclass 
	 */
	protected function subclassSetup() {}

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
				DB::getInstance()->updateWhere('conf', '`key`='.DB::getInstance()->escape($this->Key), 'value', $this->getValueAsString());
				$this->doOnchangeJobs();
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

		DB::getInstance()->insert('conf', $columns, $values);
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
		if (!empty($this->Options['unit']) || !empty($this->Options['size']) || !empty($this->Options['layout'])) {
			$Field = new FormularInput($this->getKey(), $this->getLabel(), $this->getValue());
			$Field->setUnit($this->Options['unit']);
			$Field->setSize($this->Options['size']);
			$Field->setLayout($this->Options['layout']);

			return $Field;
		}

		return new FormularInput($this->getKey(), $this->getLabel(), $this->getValue());
	}

	/**
	 * Define const
	 */
	private function defineConst() {
		if (!AccountHandler::$IS_ON_REGISTER_PROCESS && !defined('CONF_'.$this->getKey())) {
			$value = $this->getValue();

			if (!is_scalar($value))
				$value = false;

			define('CONF_'.$this->getKey(), $value);
		}
	}

	/**
	 * Do jobs after value changed 
	 */
	final protected function doOnchangeJobs() {
		$this->evaluateOnchangeCode();
		$this->setReloadFlag();
	}

	/**
	 * Evaluate onchange code (only fire if value has changed!)
	 */
	private function evaluateOnchangeCode() {
		if (!empty($this->Options['onchange_eval']))
			eval($this->Options['onchange_eval']);
	}

	/**
	 * Set reload flag (only fire if value has changed!) 
	 */
	private function setReloadFlag() {
		if (!empty($this->Options['onchange']))
			Ajax::setReloadFlag($this->Options['onchange']);
	}
}