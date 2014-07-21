<?php
/**
 * This file contains class::ImporterWindowTabFormular
 * @package Runalyze\Import
 */
/**
 * Importer tab: form
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class ImporterWindowTabFormular extends ImporterWindowTab {
	/**
	 * Training objects
	 * @var TrainingObject[]
	 */
	protected $TrainingObjects = array();

	/**
	 * Constructor
	 * @param array $TrainingObjects optional
	 */
	public function __construct(array $TrainingObjects) {
		$this->TrainingObjects = $TrainingObjects;
	}

	/**
	 * CSS id
	 * @return string
	 */
	public function cssID() {
		return 'formular';
	}

	/**
	 * Title
	 * @return string
	 */
	public function title() {
		return __('Form');
	}

	/**
	 * Display tab content
	 */
	public function displayTab() {
		if (empty($this->TrainingObjects))
			$this->displaySingleFormularFor( new TrainingObject(DataObject::$DEFAULT_ID) );
		elseif (count($this->TrainingObjects) == 1)
			$this->displaySingleFormularFor( $this->TrainingObjects[0] );
		else
			$this->displayMultipleFormular();
	}

	/**
	 * Display formular for one training
	 * @param TrainingObject $SingleObject
	 */
	protected function displaySingleFormularFor(TrainingObject $SingleObject) {
		if ($SingleObject->Weather()->isEmpty())
			$SingleObject->setWeatherForecast();

		$Formular = new TrainingFormular($SingleObject, StandardFormular::$SUBMIT_MODE_CREATE);
		$Formular->setId('training');
		$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
		$Formular->display();
	}

	/**
	 * Display formular for more than one training
	 */
	protected function displayMultipleFormular() {
		$Formular = new MultiImporterFormular();
		$Formular->setObjects($this->TrainingObjects);
		$Formular->display();
	}
}