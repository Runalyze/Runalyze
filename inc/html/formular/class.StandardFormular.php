<?php
/**
 * This file contains class::StandardFormular
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for displaying a standard formular
 * 
 * This standard formular is always connected to a given DataObject.
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class StandardFormular extends Formular {
	/**
	 * Enum: Submit mode - editing
	 * @var int 
	 */
	public static $SUBMIT_MODE_EDIT = 0;

	/**
	 * Enum: Submit mode - creating
	 * @var int 
	 */
	public static $SUBMIT_MODE_CREATE = 1;

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
	 * @param int $mode
	 */
	public function __construct(DataObject &$dataObject, $mode) {
		parent::__construct();

		$this->setMode($mode);
		$this->init($dataObject);
	}

	/**
	 * Initialize standard formular with validation and database-connection 
	 * @param DataObject $dataObject
	 */
	protected function init(DataObject &$dataObject) {
		$this->wasSubmitted = isset($_POST['submit']);
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
			$this->dataObject->setFromArray($_POST);

			if ($this->submitMode == self::$SUBMIT_MODE_CREATE) {
				$this->dataObject->insert();
			} elseif ($this->submitMode == self::$SUBMIT_MODE_EDIT) {
				$this->dataObject->update();
			}
		}

		foreach ($Failures as $message)
			$this->addFailure($message);

		if (!$this->submitSucceeded() || $this->submitMode == self::$SUBMIT_MODE_EDIT)
			$this->initFieldsets();
	}

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
			if (($this->submitMode == self::$SUBMIT_MODE_CREATE && $HiddenKey != 'id')
					|| ($this->submitMode == self::$SUBMIT_MODE_EDIT && $HiddenKey == 'id'))
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
		$String = __('Submit');

		if ($this->submitMode == self::$SUBMIT_MODE_CREATE)
			$String = __('Create');

		if ($this->submitMode == self::$SUBMIT_MODE_EDIT)
			$String = __('Save');

		$this->addSubmitButton( $String );
	}

	/**
	 * Get DatabaseScheme
	 * @return DatabaseScheme 
	 */
	protected function databaseScheme() {
		return $this->dataObject->databaseScheme();
	}

	/**
	 * Display formular
	 * 
	 * This method overwrites the method of Formular.
	 * After submitting data, the internal method displayAfterSubmit() is called.
	 */
	public function display() {
		if ($this->submitSucceeded())
			$this->displayAfterSubmit();
		else
			parent::display();
	}

	/**
	 * Display after submit
	 * 
	 * This function can be overwritten in subclasses.
	 */
	protected function displayAfterSubmit() {
		parent::display();
	}
}