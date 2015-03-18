<?php
/**
 * This file contains class::ImporterWindow
 * @package Runalyze\Import
 */

use Runalyze\Configuration;

/**
 * Window for importing/creating new trainings.
 * 
 * This class displays a window to upload files, connect to Garmin Communicator
 * or to create new trainings via form.
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class ImporterWindow {
	/**
	 * URL for window
	 * @var string
	 */
	static public $URL = 'call/call.Training.create.php';

	/**
	 * Show upload form?
	 * @var bool
	 */
	protected $showUploader = true;

	/**
	 * Tabs
	 * @var array
	 */
	protected $Tabs = array();

	/**
	 * Array with training objects
	 * @var array
	 */
	protected $TrainingObjects = array();

	/**
	 * Errors
	 * @var array
	 */
	protected $Errors = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->checkForImport();
		$this->initTabs();
	}

	/**
	 * Init tabs
	 */
	private function initTabs() {
		$this->Tabs['upload']   = new ImporterWindowTabUpload();
		$this->Tabs['garmin']   = new ImporterWindowTabCommunicator();
		$this->Tabs['formular'] = new ImporterWindowTabFormular( $this->TrainingObjects );

		if (isset($_GET['date']))
			$this->Tabs['formular']->setVisible();
		elseif (empty($this->TrainingObjects) && Configuration::ActivityForm()->creationMode()->usesUpload())
			$this->Tabs['upload']->setVisible();
		elseif (empty($this->TrainingObjects) && Configuration::ActivityForm()->creationMode()->usesGarminCommunicator())
			$this->Tabs['garmin']->setVisible();
		else
			$this->Tabs['formular']->setVisible();
	}

	/**
	 * Check for import
	 */
	private function checkForImport() {
		if (isset($_POST['forceAsFileName']))
			$this->importFile($_POST['forceAsFileName']);
		elseif (isset($_GET['file']))
			$this->importFile($_GET['file']);
		elseif (isset($_GET['files']))
			$this->importFiles($_GET['files']);
		elseif (isset($_POST['data']))
			$this->importFromGarminCommunicator();
		elseif (isset($_POST['multi-importer']))
			$this->importFromMultiImporter();
		elseif (!empty($_POST))
			$this->setObjectFromStandardFormular();
	}

	/**
	 * Import file
	 * @param string $fileName
	 */
	private function importFile($fileName) {
		$Factory = new ImporterFactory($fileName);

		$this->TrainingObjects = $Factory->trainingObjects();

		$this->Errors = array_merge($this->Errors, $Factory->getErrors());
	}

	/**
	 * Import files
	 * @param string $fileNames
	 */
	private function importFiles($fileNames) {
		$fileNames = explode(';', $fileNames);
	
		$Factory = new ImporterFactory($fileNames);

		$this->TrainingObjects = $Factory->trainingObjects();

		$this->Errors = array_merge($this->Errors, $Factory->getErrors());
	}

	/**
	 * Import form garmin communicator
	 */
	private function importFromGarminCommunicator() {
		$Factory = new ImporterFactory( ImporterFactory::$FROM_COMMUNICATOR );

		$this->TrainingObjects = $Factory->trainingObjects();

		$this->Errors = array_merge($this->Errors, $Factory->getErrors());
	}

	/**
	 * Import from MultiImporter
	 */
	private function importFromMultiImporter() {
		$Importer = new MultiImporter();
		$Importer->insertTrainings();
		$Importer->displayAfterInsert();
		exit;
	}

	/**
	 * Set object from standard formular
	 */
	private function setObjectFromStandardFormular() {
		if (isset($_POST['submit']))
			$this->TrainingObjects = array(new TrainingObject( DataObject::$DEFAULT_ID ));
	}

	/**
	 * Display the window/formular for creation
	 */
	public function display() {
		if ($this->returnJSON())
			return;

		$this->displayNavigation();
		$this->displayTabs();
		$this->displayErrors();
	}

	/**
	 * Check for JSON-return
	 * @return boolean
	 */
	private function returnJSON() {
		$Uploader = new ImporterUpload();

		if ($Uploader->thereWasAFile()) {
			echo $Uploader->getResponse();
			Error::getInstance()->debug_displayed = true;
			return true;
		}

		return false;
	}

	/**
	 * Display tab navigation
	 */
	private function displayNavigation() {
		$Links = array();

		foreach ($this->Tabs as $Tab)
			$Links[] = array('tag' => $Tab->link());

		echo '<div class="panel-menu panel-menu-floated">';
		echo Ajax::toolbarNavigation($Links);
		echo '</div>';
	}

	/**
	 * Display tabs
	 */
	private function displayTabs() {
		foreach ($this->Tabs as $Tab)
			$Tab->display();
	}

	/**
	 * Display errors
	 */
	private function displayErrors() {
		if (!empty($this->Errors))  {
			echo '<div class="panel-content">';

			foreach ($this->Errors as $Error)
				echo HTML::error($Error);

			echo '</div>';
		}
	}

	/**
	 * Get link for create window
	 */
	static public function link() {
		return Ajax::window('<a href="'.self::$URL.'" '.Ajax::tooltip('', __('Add workout'), false, true).'>'.Icon::$ADD.'</a>', 'small');
	}

	/**
	 * Get link for create window for a given date
	 * @param int $timestamp
	 * @return string
	 */
	static public function linkForDate($timestamp) {
		if ($timestamp > time()) {
			return '<span style="opacity:.25;">'.Icon::$ADD_SMALL.'</span>';
		}

		$date = date('d.m.Y', $timestamp);

		return Ajax::window('<a href="'.self::$URL.'?date='.$date.'">'.Icon::$ADD_SMALL.'</a>', 'small');
	}
}