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

	/** @var bool */
	protected $ForceMultiImporter = false;

	/**
	 * Constructor
	 * @param array $TrainingObjects optional
	 * @param bool $forceMultiImporter
	 */
	public function __construct(array $TrainingObjects, $forceMultiImporter = false) {
		$this->TrainingObjects = $TrainingObjects;
		$this->ForceMultiImporter = $forceMultiImporter;
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
		if ($this->ForceMultiImporter || count($this->TrainingObjects) > 1) {
			$this->displayMultipleFormular();
		} elseif (empty($this->TrainingObjects)) {
			$this->displaySingleFormularFor(new TrainingObject(DataObject::$DEFAULT_ID));
		} else {
			$this->displaySingleFormularFor($this->TrainingObjects[0]);
		}
	}

	/**
	 * Display formular for one training
	 * @param TrainingObject $SingleObject
	 */
	protected function displaySingleFormularFor(TrainingObject $SingleObject) {
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
