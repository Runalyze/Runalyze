<?php
/*
 * Class for a database scheme
 * @author Hannes Christiansen
 */
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
	 * Array with keys which failed validation
	 * @var array 
	 */
	protected $validationFailedKeys = array();

	/**
	 * Failure messages to display
	 * @var array 
	 */
	protected $validationFailures = array();

	/**
	 * Inserted ID
	 * @var int 
	 */
	protected $insertedId = -1;

	/**
	 * Constructor: Only allowed for DatabaseSchemePool, always use DatabaseSchemePool::get($schemeFile)
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
	 * Get ID of inserted data if succeeded
	 * @return int
	 */
	public function insertedId() {
		return $this->insertedId;
	}

	/**
	 * Try to insert from posted values, return array with failure messages
	 * @return array 
	 */
	public function tryToInsertFromPost() {
		foreach (array_keys($this->fields) as $key)
			if (isset($_POST[$key]) || $this->fieldIsRequired($key))
				$this->validatePostedValue($key);

		if (!empty($this->validationFailedKeys) && empty($this->validationFailures)) {
			$this->validationFailures[] = 'Beim Absenden des Formulars ist ein Fehler aufgetreten.';
		} else {
			$this->insertAllPostedValues();
		}

		return $this->validationFailures;
	}

	/**
	 * Insert all posted values, be sure validation has been done 
	 */
	protected function insertAllPostedValues() {
		$columns = array();
		$values  = array();

		foreach (array_keys($this->fields) as $key)
			if ($key != 'id' && isset($_POST[$key])) {
				$columns[] = $key;
				$values[]  = $_POST[$key];
			}

		$this->insertedId = Mysql::getInstance()->insert($this->tableName(), $columns, $values);

		if ($this->insertedId === false)
			$this->validationFailures[] = 'Unbekannter Fehler: '.mysql_error();
	}

	/**
	 * Try to update from posted values, return array with failure messages
	 * @return array 
	 */
	public function tryToUpdateFromPost() {
		foreach (array_keys($this->fields) as $key)
			if (isset($_POST[$key]) || $this->fieldIsRequired($key))
				$this->validatePostedValue($key);

		if (!empty($this->validationFailedKeys) && empty($this->validationFailures)) {
			$this->validationFailures[] = 'Beim Absenden des Formulars ist ein Fehler aufgetreten.';
		} else {
			$this->updateAllPostedValues();
		}

		return $this->validationFailures;
	}

	/**
	 * Update all posted values, be sure validation has been done 
	 */
	protected function updateAllPostedValues() {
		$columns = array();
		$values  = array();

		foreach (array_keys($this->fields) as $key)
			if ($this->fieldParser($key) == FormularValueParser::$PARSER_BOOL) {
				$columns[] = $key;
				$values[]  = isset($_POST[$key]) ? 1 : 0;
			} elseif ($key != 'id' && isset($_POST[$key])) {
				$columns[] = $key;
				$values[]  = $_POST[$key];
			}

		Mysql::getInstance()->update($this->tableName(), Request::sendId(), $columns, $values);
	}

	/**
	 * Try to validate a posted value
	 * @param string $fieldKey
	 */
	protected function validatePostedValue($fieldKey) {
		$validation = FormularValueParser::validatePost($fieldKey, $this->fieldParser($fieldKey), $this->fieldParserOptions($fieldKey));
	
		if ($validation !== true)
			$this->validationFailedKeys[] = $fieldKey;

		if (is_string($validation))
			$this->validationFailures[] = $validation;
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
	 * Create a new field for a given key
	 * @param FormularField $fieldKey 
	 */
	public function FieldFor($fieldKey) {
		$label = $this->fieldLabel($fieldKey);
		$unit  = $this->fieldUnit($fieldKey);
		$size  = $this->fieldSize($fieldKey);

		if ($this->fieldParser($fieldKey) == FormularValueParser::$PARSER_BOOL)
			$Field = new FormularCheckbox($fieldKey, $label);
		else {
			switch ($this->fieldType($fieldKey)) {
				default:
					$Field = new FormularInput($fieldKey, $label);
					break;
			}
		}

		$Field->setParser( $this->fieldParser($fieldKey), $this->fieldParserOptions($fieldKey) );

		if (!empty($unit))
			$Field->setUnit($unit);

		if (!empty($size))
			$Field->setSize($size);

		if (in_array($fieldKey, $this->validationFailedKeys))
			$Field->addCSSclass(FormularField::$CSS_VALIDATION_FAILED);

		return $Field;
	}

	/**
	 * Set default parser for all 
	 */
	protected function setDefaultParser() {
		foreach ($this->fields as $key => &$options) {
			if (!isset($options['formular']['parser'])) {
				switch ($this->fieldType($key)) {
					case 'varchar':
					case 'text':
					case 'longtext':
					case 'tinytext':
						$options['formular']['parser'] = FormularValueParser::$PARSER_STRING;
						break;
					case 'int':
					case 'smallint':
					case 'tinyint':
						$options['formular']['parser'] = FormularValueParser::$PARSER_INT;
						if (isset($options['database']['precision']))
							$options['formular']['parserOptions'] = array('precision' => $options['database']['precision']);
					case 'decimal':
						$options['formular']['parser'] = FormularValueParser::$PARSER_DECIMAL;
						if (isset($options['database']['precision']))
							$options['formular']['parserOptions'] = array('precision' => $options['database']['precision']);
						break;
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

		switch ($this->fieldType($fieldKey)) {
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

	/**
	 * Get type for a given key
	 * @param string $fieldKey
	 * @return string
	 */
	public function fieldType($fieldKey) {
		return @$this->fields[$fieldKey]['database']['type'];
	}

	/**
	 * Get label for a given key
	 * @param string $fieldKey
	 * @return string
	 */
	public function fieldLabel($fieldKey) {
		if (isset($this->fields[$fieldKey]['formular']['label']))
			return $this->fields[$fieldKey]['formular']['label'];

		return '';
	}

	/**
	 * Get unit for a given key
	 * @param string $fieldKey
	 * @return string
	 */
	public function fieldUnit($fieldKey) {
		if (isset($this->fields[$fieldKey]['formular']['unit']))
			return $this->fields[$fieldKey]['formular']['unit'];

		return '';
	}

	/**
	 * Get size for a given key
	 * @param string $fieldKey
	 * @return mixed
	 */
	public function fieldSize($fieldKey) {
		if (isset($this->fields[$fieldKey]['formular']['size']))
			return $this->fields[$fieldKey]['formular']['size'];

		return '';
	}

	/**
	 * Is this field required?
	 * @param string $fieldKey
	 * @return boolean
	 */
	public function fieldIsRequired($fieldKey) {
		if (isset($this->fields[$fieldKey]['formular']['required']))
			return $this->fields[$fieldKey]['formular']['required'];

		return false;
	}

	/**
	 * Get parser for a given key
	 * @param string $fieldKey
	 * @return mixed
	 */
	public function fieldParser($fieldKey) {
		if (isset($this->fields[$fieldKey]['formular']['parser']))
			return $this->fields[$fieldKey]['formular']['parser'];

		return null;
	}

	/**
	 * Get parser for a given key
	 * @param string $fieldKey
	 * @return array
	 */
	public function fieldParserOptions($fieldKey) {
		if (isset($this->fields[$fieldKey]['formular']['parserOptions']))
			return $this->fields[$fieldKey]['formular']['parserOptions'];

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
}