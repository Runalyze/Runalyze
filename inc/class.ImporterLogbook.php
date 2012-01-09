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
	 * @var Plugin
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
		$FileContent = $this->getFileContentAsString();
		$Parser      = new XmlParser($FileContent);
		$this->XML   = $Parser->getContentAsArray();

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
			echo HTML::em('Die Trainings wurden importiert.').'<br /><br />';
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
		if (!isset($this->XML['logbook'])) {
			$this->Errors[] = 'Es scheint keine korrekte Logbook-Datei zu sein.';
			return;
		}

		$this->XML = $this->XML['logbook'];

		$this->parseCategories();
		$this->parseActivities();
	}

	/**
	 * Parse all categories in xml-file
	 */
	protected function parseCategories() {
		if (!isset($this->XML['activitycategories']) || !isset($this->XML['activitycategories']['category']))
			return;

		foreach ($this->XML['activitycategories']['category'][0]['categories']['activitycategory'] as $key => $Category) {
			if ($key == 'attr' && isset($Category['name']))
				$this->Categories[$Category['referenceId']] = $Category['name'];

			if (!isset($Category['attr']) || !isset($Category['attr']['name']))
				break;

			$this->Categories[$Category['attr']['referenceId']] = $Category['attr']['name'];
		}
	}

	/**
	 * Parse all activities in xml-file
	 */
	protected function parseActivities() {
		if (!isset($this->XML['activities']))
			return;

		if (!isset($this->XML['activities']['activity']['attr']))
			$this->XML['activities'] = $this->XML['activities']['activity'];

		foreach ($this->XML['activities'] as $key => $Activity)
			$this->createTrainingFromXML($Activity);
	}

	/**
	 * Create new Training-object for a given xml-part
	 * @param array $XML
	 * @return Training
	 */
	protected function createTrainingFromXML($XML) {
		if (!isset($XML['attr']))
			return;

		$Attr          = $XML['attr'];
		$categoryName  = isset($Attr['categoryName'])  ? $Attr['categoryName']              : '?';
		$startTime     = isset($Attr['startTime'])     ? strtotime($Attr['startTime'])      : time();
		$location      = isset($Attr['location'])      ? $Attr['location']                  : '';
		$totalCalories = isset($Attr['totalCalories']) ? $Attr['totalCalories']             : 0;
		$totalDistance = isset($Attr['totalDistance']) ? round($Attr['totalDistance'])/1000 : 0;
		$totalTime     = isset($Attr['totalTime'])     ? round($Attr['totalTime'])          : 0;

		$Training = new Training(Training::$CONSTRUCTOR_ID);
		$Training->set('sportid', self::getIDforDatabaseString('sport', $categoryName));
		$Training->set('time', $startTime);
		$Training->set('route', $location);
		$Training->set('kcal', $totalCalories);
		$Training->set('distance', $totalDistance);
		$Training->set('s', $totalTime);

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
	 * @param mixed $default
	 * @return double
	 */
	static protected function getNumericFromString($string, $default = 0) {
		$array  = explode(' ', trim($string));
		$string = preg_replace('/[^0-9\.,]/Uis', '', $array[0]);

		return (double)$string;
	}
}
?>