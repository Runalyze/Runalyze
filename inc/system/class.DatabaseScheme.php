<?php
/**
 * This file contains class::DatabaseScheme
 * @package Runalyze\DataObjects
 */
/**
 * Class for a database scheme
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects
 */
use Runalyze\Error;

class DatabaseScheme {
	/**
	 * Scheme file
	 * @var string
	 */
	protected $schemeFile = '';

	/**
	 * Tablename
	 * @var string 
	 */
	protected $tableName = '';

	/**
	 * Array for all fields
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Array for all fieldsets
	 * @var array
	 */
	protected $fieldsets = array();

	/**
	 * Array with all hidden keys
	 * @var array 
	 */
	protected $hiddenKeys = array();

	/**
	 * Failure messages to display
	 * @var array 
	 */
	protected $validationFailures = array();

	/**
	 * Constructor: Only allowed for DatabaseSchemePool
	 * 
	 * Always use DatabaseSchemePool::get($schemeFile) to get a DatabaseScheme!
	 * @param string $schemeFile 
	 */
	public function __construct($schemeFile) {
		$this->schemeFile = FRONTEND_PATH.$schemeFile;
		$this->loadDefaultScheme();
		$this->loadFile();
		$this->setDefaultParser();
	}

	/**
	 * Load default scheme 
	 */
	protected function loadDefaultScheme() {
		include FRONTEND_PATH.'/system/schemes/scheme.default.php';

		$this->fields     = $FIELDS;
		$this->fieldsets  = $FIELDSETS;
		$this->hiddenKeys = $HIDDEN_KEYS;
	}

	/**
	 * Load file
	 */
	protected function loadFile() {
		if (!file_exists($this->schemeFile)) {
			Error::getInstance()->addError('Cannot find database scheme: '.$this->schemeFile);
			return;
		} else {
			include $this->schemeFile;

			if (!isset($TABLENAME) || !isset($FIELDS) || !isset($FIELDSETS)) {
				Error::getInstance()->addError('$TABLENAME, $FIELDS and $FIELDSETS must be defined in scheme file: '.$this->schemeFile);
			} else {
				$this->tableName  = PREFIX.$TABLENAME;
				$this->fields     = array_merge($this->fields, $FIELDS);
				$this->fieldsets  = array_merge($this->fieldsets, $FIELDSETS);
				$this->hiddenKeys = array_merge($this->hiddenKeys, $HIDDEN_KEYS);
			}
		}
	}

	/**
	 * Check database structure 
	 */
	public function checkDatabaseStructure() {
		// TODO
	}

	/**
	 * Correct database structure 
	 */
	public function correctDatabaseStructure() {
		// TODO
	}

	/**
	 * Get tablename
	 * @return string 
	 */
	public function tableName() {
		return $this->tableName;
	}

	/**
	 * Get default array of scheme 
	 * @return array
	 */
	public function getDefaultArray() {
		$array = array();

		foreach (array_keys($this->fields) as $key)
			$array[$key] = $this->fieldDefaultValue($key);

		return $array;
	}

	/**
	 * Check if there is a field for timestamp
	 * @return boolean 
	 */
	public function hasTimestamp() {
		return isset($this->fields['time']);
	}

	/**
	 * Get all hidden keys
	 * @return array 
	 */
	public function hiddenKeys() {
		return $this->hiddenKeys;
	}

	/**
	 * Hide a fieldset
	 * @param string $id 
	 */
	public function hideFieldset($id) {
		foreach ($this->fieldsets as &$Fieldset)
			if ($Fieldset['id'] == $id)
				$Fieldset['hidden'] = true;
	}

	/**
	 * Remove a field
	 * @param string $fieldKey 
	 */
	public function hideField($fieldKey) {
		$this->fields[$fieldKey]['hidden'] = true;
	}

	/**
	 * Get all visible fieldsets
	 * @return array
	 */
	public function fieldsets() {
		$Fieldsets = array();

		foreach ($this->fieldsets as $Fieldset)
			if (!isset($Fieldset['hidden']) || !$Fieldset['hidden'])
				$Fieldsets[] = $Fieldset;

		return $Fieldsets;
	}

	/**
	 * Get field
	 * @param string $key
	 * @return array
	 */
	public function field($key) {
		if (isset($this->fields[$key]))
			return $this->fields[$key];

		return array();
	}

	/**
	 * Is this field hidden
	 * @param string $fieldKey
	 * @return boolean
	 */
	public function fieldIsHidden($fieldKey) {
		return isset($this->fields[$fieldKey]['hidden']) && $this->fields[$fieldKey]['hidden'];
	}

	/**
	 * Set default parser for all 
	 */
	protected function setDefaultParser() {
		foreach ($this->fields as $key => &$options) {
			if (!isset($options['formular']['parser']) && !isset($options['formular']['class'])) {
				$parserOptions = array();

				if (isset($options['database']['null']))
					$parserOptions['null'] = $options['database']['null'];
				if (isset($options['database']['precision']))
					$parserOptions['precision'] = $options['database']['precision'];

				if (isset($options['formular']['notempty']))
					$parserOptions['notempty'] = $options['formular']['notempty'];

				$options['formular']['parserOptions'] = $parserOptions;

				switch ($this->fields[$key]['database']['type']) {
					case 'varchar':
					case 'text':
					case 'longtext':
					case 'tinytext':
						$options['formular']['parser'] = FormularValueParser::$PARSER_STRING;
						break;

					case 'int':
					case 'smallint':
					case 'tinyint':
						if (isset($options['database']['precision']) && $options['database']['precision'] == 1)
							break;

						$options['formular']['parser'] = FormularValueParser::$PARSER_INT;
						break;

					case 'decimal':
					case 'float':
						$options['formular']['parser'] = FormularValueParser::$PARSER_DECIMAL;
						break;

					case 'enum': // TODO
						break;

					case 'set':
						break;
				}
			}
		}
	}

	/**
	 * Get default value
	 * @param string $fieldKey 
	 * @return mixed
	 */
	protected function fieldDefaultValue($fieldKey) {
		if (isset($this->fields[$fieldKey]['database']['default']))
			return $this->fields[$fieldKey]['database']['default'];

		if ($fieldKey == 'time')
			return time();

		if (isset($this->fields[$fieldKey]['database']['null']))
			return null;

		switch ($this->fields[$fieldKey]['database']['type']) {
			case 'int':
			case 'smallint':
			case 'tinyint':
			case 'decimal':
			case 'float':
				return 0;
			default:
				return '';
		}
	}
}