<?php
/**
 * Class for formular to create a new training
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class TrainingCreatorFormular extends TrainingEditorFormular {
	/**
	 * Constructor 
	 */
	public function __construct($sportid = -1) {
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

		$Forecaster = Weather::Forecaster();
		$Forecaster->setPostDataIfEmpty();
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
		if (!$this->Training->Sport()->hasTypes()) // Ignore if type has splits because type isn't set
			return;

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