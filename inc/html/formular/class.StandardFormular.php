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

		// TODO:
		// User DataObject::update() / DataObject::insert()
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
	 * Init fieldsets
	 */
	protected function initFieldsets() {
		$this->fieldsets = array();

		$Scheme = &$this->dataObject->databaseSchemeReference();

		$FieldsetFactory = new StandardFormularFieldsetFactory($Scheme);
		$FieldsetFactory->addFieldsets($this);
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