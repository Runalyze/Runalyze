<?php
/**
 * This file contains the class::ImporterLogbook for importing trainings from SportTracks-Logbook
 */
/**
 * Class: ImporterLogbook
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 */
class ImporterLogbook extends Importer {
	/**
	 * Filecontent as xml-array
	 * @var array
	 */
	protected $XML = array();

	/**
	 * Categories
	 * @var array
	 */
	protected $Categories = array();

	/**
	 * Array of Training-objects
	 * @var array
	 */
	protected $Trainings = array();

	/**
	 * Plugin for MultiEditor
	 * @var RunalyzePluginTool_MultiEditor
	 */
	protected $MultiEditor = null;

	/**
	 * Boolean flag for having inserted all trainings
	 * @var bool
	 */
	protected $inserted = false;

	/**
	 * Array with all errors
	 * @var array
	 */
	protected $Errors = array();


	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		$this->XML = simplexml_load_string_utf8($this->getFileContentAsString());
		$this->parseXML();
		$this->createAllTrainings();
	}

	/**
	 * Overwrite standard method to have own formular
	 */
	public function displayHTMLformular() {
		if (!empty($this->Errors))
			$this->displayErrors();
		elseif (!is_null($this->MultiEditor)) {
			$this->MultiEditor->showImportedMessage();
			$this->MultiEditor->display();
		}
	}

	/**
	 * Display error-messages
	 */
	protected function displayErrors() {
		echo '<h1>Probleme beim Import des SportTracks-Logbook</h1>';
		echo HTML::em('Beim Importieren ist ein Fehler aufgetreten.');
		echo HTML::clearBreak();

		foreach ($this->Errors as $Error)
			echo HTML::error($Error);
	}

	/**
	 * Create all trainings
	 */
	protected function createAllTrainings() {
		if (empty($this->Trainings))
			return;

		$IDs = array();

		foreach ($this->Trainings as $Training) {
			$_POST = array();
			$Training->overwritePostArray();
			$this->setCreatorToFileUpload(false);

			$Importer = Importer::getInstance();
			$Importer->setTrainingValues();
			$Importer->parsePostData();
			$Importer->insertTraining();

			$IDs[] = $Importer->insertedID;
		}

		$this->forwardToMultiEditor($IDs);
	}

	/**
	 * Forward to MultiEditor
	 * @param array $IDs
	 */
	protected function forwardToMultiEditor($IDs) {
		if (empty($IDs))
			return;

		$_GET['ids'] = implode(',', $IDs);
		
		$this->inserted = true;
		$this->MultiEditor = Plugin::getInstanceFor('RunalyzePluginTool_MultiEditor');
	}

	/**
	 * Parse internal XML-array
	 */
	protected function parseXML() {
		if (empty($this->XML->Activities)) {
			$this->Errors[] = 'Es scheint keine korrekte Logbook-Datei zu sein.';
			return;
		}

		$this->parseCategories();
		$this->parseActivities();
	}

	/**
	 * Parse all categories in xml-file
	 */
	protected function parseCategories() {
		if (empty($this->XML->ActivityCategories))
			return;

		foreach ($this->XML->xpath('//ActivityCategory/Categories/ActivityCategory') as $Category) {
			if (!isset($Category['referenceId']) || !isset($Category['name']))
				break;

			$this->Categories[(string)$Category['referenceId']] = (string)$Category['name'];
		}
	}

	/**
	 * Parse all activities in xml-file
	 */
	protected function parseActivities() {
		$Activities = $this->XML->xpath('//Activity');

		foreach ($Activities as $Activity)
			$this->createTrainingFromXML($Activity);
	}

	/**
	 * Create new Training-object for a given xml-part
	 * @param array $XML
	 * @return Training
	 */
	protected function createTrainingFromXML($XML) {
		$categoryName  = isset($XML['categoryName'])     ? (string)$XML['categoryName']           : '?';
		$startTime     = isset($XML['startTime'])        ? strtotime((string)$XML['startTime'])   : time();
		$comment       = isset($XML['name'])             ? (string)$XML['name']                   : '';
		$location      = isset($XML['location'])         ? (string)$XML['location']               : '';
		$totalCalories = isset($XML['totalCalories'])    ? (int)$XML['totalCalories']             : 0;
		$totalDistance = isset($XML['totalDistance'])    ? round((int)$XML['totalDistance'])/1000 : 0;
		$totalTime     = isset($XML['totalTime'])        ? round((int)$XML['totalTime'])          : 0;
		$elevation     = isset($XML['totalAscend'])      ? round((int)$XML['totalAscend'])        : 0;
		$pulse_avg     = isset($XML['averageHeartRate']) ? round((int)$XML['averageHeartRate'])   : 0;
		$pulse_max     = isset($XML['maximumHeartRate']) ? round((int)$XML['maximumHeartRate'])   : 0;

		$Training = new Training(Training::$CONSTRUCTOR_ID);
		$Training->set('sportid', self::getIDforDatabaseString('sport', $categoryName));
		$Training->set('time', $startTime);
		$Training->set('comment', $comment);
		$Training->set('route', $location);
		$Training->set('kcal', $totalCalories);
		$Training->set('distance', $totalDistance);
		$Training->set('s', $totalTime);
		$Training->set('elevation', $elevation);
		$Training->set('pulse_avg', $pulse_avg);
		$Training->set('pulse_max', $pulse_max);

		$this->Trainings[] = $Training;
	}

	/**
	 * Search in database for a string and get the ID
	 * @param string $table
	 * @param string $string
	 * @return int
	 */
	static protected function getIDforDatabaseString($table, $string) {
		$SearchQuery = 'SELECT id FROM '.PREFIX.$table.' WHERE name LIKE "%'.$string.'%" LIMIT 1';
		$Result = Mysql::getInstance()->fetchSingle($SearchQuery);

		if ($Result === false) {
			if ($table == 'sport')
				return CONF_MAINSPORT;

			return 0;
		}

		return $Result['id'];
	}

	/**
	 * Get numeric part of a string
	 * @param string $string
	 * @return double
	 */
	static protected function getNumericFromString($string) {
		$array  = explode(' ', trim($string));
		$string = preg_replace('/[^0-9\.,]/Uis', '', $array[0]);

		return (double)$string;
	}
}