<?php
/**
 * This file contains class::MultiImporter
 * @package Runalyze\Import
 */

use Runalyze\Model\Activity;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity\Dataview;

/**
 * Multi importer
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class MultiImporter {
	/**
	 * Number of trainings
	 * @var int
	 */
	protected $NumberOfTrainings = 0;

	/**
	 * Inserted IDs
	 * @var array
	 */
	protected $InsertedIDs = array();

	/**
	 * Editor requested?
	 * @var bool
	 */
	protected $EditorRequested = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->NumberOfTrainings = Request::param('number-of-trainings');
		$this->EditorRequested   = isset($_POST['multi-edit']);

		if (!isset($_POST['training-import']))
			$this->NumberOfTrainings = 0;
	}

	/**
	 * Insert trainings from post
	 */
	public function insertTrainings() {
		for ($i = 0; $i < $this->NumberOfTrainings; $i++)
			if (isset($_POST['training-import'][$i]) && $_POST['training-import'][$i] == 'on')
				$this->insertTraining($i);
	}

	/**
	 * Insert training
	 * @param int $i
	 */
	protected function insertTraining($i) {
		if (!isset($_POST['training-data'][$i]))
			return;

		$Data = unserialize(urldecode($_POST['training-data'][$i]));
		$Training = new TrainingObject( DataObject::$DEFAULT_ID );
		$Training->setFromArray($Data);
		$Training->setWeatherForecast();
		$Training->insert();

		$this->InsertedIDs[] = $Training->id();
	}

	/**
	 * Display after insert
	 */
	public function displayAfterInsert() {
		if ($this->EditorRequested) {
			if (count($this->InsertedIDs) == 1) {
				$this->displaySingleEditor($this->InsertedIDs[0]);
			} else {
				$MultiEditor = new MultiEditor($this->InsertedIDs);
				$MultiEditor->display();
			}
		} else {
			echo HTML::em( __('The activities have been successfully imported.') );
			echo Ajax::closeOverlay();
		}
	}

	/**
	 * Display editor for single activity
	 * @param int $id
	 */
	protected function displaySingleEditor($id) {
		header('Location: call.Training.edit.php?id='.$id);
	}
}