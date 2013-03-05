<?php
/**
 * Class for displaying a standard formular
 * @author Hannes Christiansen 
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
	protected function init($dataObject) {
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
			if ($this->submitMode == self::$SUBMIT_MODE_CREATE)
				$Failures = $this->databaseScheme()->tryToInsertFromPost();
			elseif ($this->submitMode == self::$SUBMIT_MODE_EDIT)
				$Failures = $this->databaseScheme()->tryToUpdateFromPost();
		}

		foreach ($Failures as $message)
			$this->addFailure($message);

		// What the fuck? Why again?
		//if (!$this->submitSucceeded())
		//	$this->initFieldsets();
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
			$Fieldset->setTitle($FieldsetArray['legend']);

			$this->initFields($Fieldset, $FieldsetArray['fields']);

			if (isset($FieldsetArray['layout']))
				$Fieldset->setLayoutForFields($FieldsetArray['layout']);

			$this->addFieldset($Fieldset);
		}
	}

	/**
	 * Init fields for a given fieldset
	 * @param FormularFieldset $Fieldset
	 * @param array $FieldKeys 
	 */
	protected function initFields(&$Fieldset, $FieldKeys) {
		foreach ($FieldKeys as $Key)
			if (!$this->databaseScheme()->fieldIsHidden($Key))
				$Fieldset->addField( $this->databaseScheme()->FieldFor($Key) );
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
?>