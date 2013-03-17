<?php
/**
 * This file contains class::StandardFormular
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for displaying a standard formular
 * 
 * This standard formular is always connected to a given DataObject.
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @package Runalyze\HTML\Formular
 */
class StandardFormular extends Formular {
	/**
	 * Array with submit strings for modes from enum
	 * @var array 
	 */
	static public $SUBMIT_STRINGS = array('Bearbeiten', 'Eintragen');

	/**
	 * Enum: Submit mode - editing
	 * @var int 
	 */
	static public $SUBMIT_MODE_EDIT = 0;

	/**
	 * Enum: Submit mode - creating
	 * @var int 
	 */
	static public $SUBMIT_MODE_CREATE = 1;

	/**
	 * Boolean flag: Has the formular been submitted?
	 * @var boolean 
	 */
	protected $wasSubmitted = false;

	/**
	 * Submit mode
	 * @var int 
	 */
	protected $submitMode = 0;

	/**
	 * Relating DataObject
	 * @var DataObject 
	 */
	protected $dataObject = null;

	/**
	 * Construct a new formular
	 * @param DataObject $dataObject
	 * @param enum $mode
	 */
	public function __construct($dataObject, $mode) {
		parent::__construct();

		$this->setMode($mode);
		$this->init($dataObject);
	}

	/**
	 * Initialize standard formular with validation and database-connection 
	 * @param DataObject $dataObject
	 */
	protected function init(DataObject $dataObject) {
		$this->wasSubmitted = !empty($_POST);
		$this->dataObject   = $dataObject;

		if (!$this->wasSubmitted)
			$this->dataObject->setValuesAsPostData();

		$this->addCSSclass('ajax');
		$this->addAttribute('onsubmit', 'return false;');

		$this->initHiddenKeys();
		$this->initFieldsets();
		$this->checkForSubmit();
	}

	/**
	 * Set submit mode
	 * @param int $submitMode 
	 */
	public function setMode($submitMode) {
		$this->submitMode = $submitMode;
	}

	/**
	 * Check for submit, therefore all fields must be set 
	 */
	protected function checkForSubmit() {
		if (!$this->wasSubmitted)
			return;

		$this->validateAllFieldsets();

		$Failures = FormularField::getValidationFailures();

		if (empty($Failures)) {
			if ($this->submitMode == self::$SUBMIT_MODE_CREATE) {
				$this->tasksBeforeInsert();
				$Failures = $this->databaseScheme()->tryToInsertFromPost();
				$this->tasksAfterInsert();
			} elseif ($this->submitMode == self::$SUBMIT_MODE_EDIT) {
				$this->tasksBeforeUpdate();
				$Failures = $this->databaseScheme()->tryToUpdateFromPost();
				$this->tasksAfterUpdate();
			}
		}

		foreach ($Failures as $message)
			$this->addFailure($message);

		if (!$this->submitSucceeded())
			$this->initFieldsets();
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
	 * Has the submit succeeded?
	 * @return boolean
	 */
	public function submitSucceeded() {
		return $this->wasSubmitted && empty($this->failures);
	}

	/**
	 * Add all hidden values 
	 */
	protected function initHiddenKeys() {
		foreach ($this->databaseScheme()->hiddenKeys() as $HiddenKey)
			if ($HiddenKey != 'id' || $this->submitMode != self::$SUBMIT_MODE_CREATE)
				$this->addHiddenValue($HiddenKey, $_POST[$HiddenKey]);
	}

	/**
	 * Init all fieldsets
	 */
	protected function initFieldsets() {
		$this->fieldsets = array();

		foreach ($this->databaseScheme()->fieldsets() as $FieldsetArray) {
			$Fieldset = new FormularFieldset();

			$this->initFields($Fieldset, $FieldsetArray['fields']);
			$this->setAttributesToFieldset($Fieldset, $FieldsetArray);
			$this->addFieldset($Fieldset);
		}
	}

	/**
	 * Set attributes to fieldset
	 * @param Fieldset $Fieldset
	 * @param array $FieldsetArray
	 */
	protected function setAttributesToFieldset(&$Fieldset, $FieldsetArray) {
			$Fieldset->setTitle($FieldsetArray['legend']);

			if (isset($FieldsetArray['layout']))
				$Fieldset->setLayoutForFields($FieldsetArray['layout']);

			if (isset($FieldsetArray['css']))
				$Fieldset->addCSSclass($FieldsetArray['css']);

			if (isset($FieldsetArray['conf']))
				$Fieldset->setConfValueToSaveStatus ($FieldsetArray['conf']);
	}

	/**
	 * Init fields for a given fieldset
	 * @param FormularFieldset $Fieldset
	 * @param array $FieldKeys 
	 */
	protected function initFields(FormularFieldset &$Fieldset, $FieldKeys) {
		foreach ($FieldKeys as $Key)
			if (!$this->databaseScheme()->fieldIsHidden($Key))
				$Fieldset->addField( $this->getFieldFor($Key) );
	}

	/**
	 * Get Field for key
	 * @param string $Key
	 * @return FormularField
	 */
	protected function getFieldFor($Key) {
		$FieldArray = $this->databaseScheme()->field($Key);

		$Field = $this->createFieldFor($Key, $FieldArray);
		$this->setAttributesToField($Field, $Key, $FieldArray);

		return $Field;
	}

	/**
	 * Create a field
	 * @param string $Key
	 * @param array $FieldArray
	 * @return object
	 */
	protected function createFieldFor($Key, $FieldArray) {
		$ClassName = $this->fieldClass($FieldArray);

		return new $ClassName($Key, $FieldArray['formular']['label']);
	}

	/**
	 * Set attributes to field
	 * @param FormularField $Field
	 * @param string $Key
	 * @param array $FieldArray
	 */
	private function setAttributesToField(FormularField &$Field, $Key, $FieldArray) {
		if (isset($FieldArray['formular']['parser'])) {
			$Options = array();

			if (isset($FieldArray['formular']['required']))
				$Options['required'] = $FieldArray['formular']['required'];

			if (isset($FieldArray['formular']['parserOptions']))
				$Options = array_merge($Options, $FieldArray['formular']['parserOptions']);

			$Field->setParser( $FieldArray['formular']['parser'], $Options );
		}

		if (isset($FieldArray['formular']['unit']))
			$Field->setUnit($FieldArray['formular']['unit']);

		if (isset($FieldArray['formular']['size']))
			$Field->setSize($FieldArray['formular']['size']);

		if (isset($FieldArray['formular']['css']))
			$Field->addLayoutClass($FieldArray['formular']['css']);

		if (isset($FieldArray['formular']['layout']))
			$Field->setLayout($FieldArray['formular']['layout']);

		if ($this->fieldClass($FieldArray) == 'FormularSelectDb')
			$Field->loadOptionsFrom($FieldArray['formular']['table'], $FieldArray['formular']['column']);
	}

	/**
	 * Get class name for a field
	 * @param array $FieldArray
	 * @return string
	 */
	private function fieldClass($FieldArray) {
		if (isset($FieldArray['formular']['class']))
			if (class_exists($FieldArray['formular']['class']))
				return $FieldArray['formular']['class'];

		return 'FormularInput';
	}

	/**
	 * Additional preparation 
	 */
	protected function prepareForDisplayInSublcass() {
		$this->addSubmitButton( self::$SUBMIT_STRINGS[$this->submitMode] );
	}

	/**
	 * Get DatabaseScheme
	 * @return DatabaseScheme 
	 */
	protected function databaseScheme() {
		return $this->dataObject->databaseScheme();
	}
}