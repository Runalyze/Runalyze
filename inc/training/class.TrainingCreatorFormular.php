<?php
/**
 * Class for formular to create a new training
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class TrainingCreatorFormular extends TrainingEditorFormular {
	/**
	 * CSS class for inputs only for running
	 * @var string
	 */
	static public $ONLY_RUNNING_CLASS = "only-running";

	/**
	 * CSS class for inputs only for sports outside
	 * @var string
	 */
	static public $ONLY_OUTSIDE_CLASS = "only-outside";

	/**
	 * CSS class for inputs only for sports with types
	 * @var string
	 */
	static public $ONLY_TYPES_CLASS = "only-types";

	/**
	 * CSS class for inputs only for sports with distance
	 * @var string
	 */
	static public $ONLY_DISTANCES_CLASS = "only-distances";

	/**
	 * Constructor 
	 */
	public function __construct($sportid = -1) {
		$this->forceToShowAllFieldsets = true;

		$Sport = new Sport($sportid);

		if (!$Sport->isValid()) {
			Error::getInstance()->addError('TrainingCreatorFormular needs valid sportid as parameter, "'.$sportid.'" was given.');
			return;
		}

		$this->initDefaultValues();
		$this->initTraining($sportid);
		$this->initFormular();
	}

	/**
	 * Init default values 
	 */
	protected function initDefaultValues() {
		if (!isset($_POST['datum']) && !isset($_POST['time']))
			$_POST['datum'] = date('d.m.Y');

		if ($this->trainingWasToday()) {
			$Weather = Weather::Forecaster();
			$Weather->setPostDataIfEmpty();
		}
	}

	/**
	 * Is the training less than 24h old?
	 * @return bool
	 */
	private function trainingWasToday() {
		if (empty($_POST) || (isset($_POST['datum']) && $_POST['datum'] == date("d.m.Y")))
			return true;

		if (!isset($_POST['time']))
			return false;

		return ($_POST['time'] > 0 && (time() - $_POST['time']) < DAY_IN_S) ;
	}

	/**
	 * Init Training
	 * @param int $sportid 
	 */
	protected function initTraining($sportid) {
		$_POST['sportid'] = $sportid;
		$this->Formular   = new Formular('call/call.Training.create.php');
		$this->Training   = new Training(Training::$CONSTRUCTOR_ID, $_POST);
	}

	/**
	 * Overwrite header 
	 */
	protected function displayHeader() {
		echo HTML::h1('Training hinzuf&uuml;gen');
	}

	/**
	 * Overwrite hidden fieldset
	 */
	protected function initHiddenFields() {
		$this->Formular->addHiddenValue('type', 'newtraining');
		$this->Formular->addHiddenValue('activity_id');
		$this->Formular->addHiddenValue('creator');
		$this->Formular->addHiddenValue('creator_details');
		$this->Formular->addHiddenValue('kcalPerHour', $this->Training->Sport()->kcalPerHour());
		$this->Formular->addHiddenValue('arr_time');
		$this->Formular->addHiddenValue('arr_lat');
		$this->Formular->addHiddenValue('arr_lon');
		$this->Formular->addHiddenValue('arr_alt');
		$this->Formular->addHiddenValue('arr_dist');
		$this->Formular->addHiddenValue('arr_heart');
		$this->Formular->addHiddenValue('arr_pace');
	}

	/**
	 * Init attributes 
	 */
	protected function initFormularAttributes() {
		$this->Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
		$this->Formular->setId('newtraining');
		$this->Formular->addCSSclass('ajax');
		$this->Formular->addAttribute('onsubmit', 'return false;');
		$this->Formular->addSubmitButton('Erstellen');
	}

	/**
	 * Overwrite fieldset for splits
	 */
	protected function initSplitsFieldset() {
		$Splits   = new Splits( Splits::$FROM_POST );

		$Fieldset = $Splits->getFieldset();
		$Fieldset->setCollapsed();

		$this->Formular->addFieldset( $Fieldset );
		$this->Formular->addHiddenValue('splits_sent', 'true');
	}

	/**
	 * Overwrite delete fieldset 
	 */
	protected function initDeleteFieldset() {
		// No delete fieldset for creator
	}

	/**
	 * Overwrite gps fieldset 
	 */
	protected function initGPSFieldset() {
		// No gps fieldset for creator
	}
}