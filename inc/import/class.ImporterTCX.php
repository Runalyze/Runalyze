<?php
/**
 * This file contains the class::ImporterTCX for importing a training from TCX
 */
/**
 * Class: ImporterTCX
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ImporterTCX extends Importer {
	/**
	 * Parsed XML
	 * @var SimpleXmlElement
	 */
	private $XML;

	/**
	 * Parsed XML
	 * @var SimpleXmlElement
	 */
	private $CompleteXML;

	/**
	 * Internal array for all arrays
	 * @var array
	 */
	private $data = array();

	/**
	 * Starttime, can be changed for pauses
	 * @var int
	 */
	private $starttime = 0;

	/**
	 * Calories
	 * @var int
	 */
	private $calories = 0;

	/**
	 * Last point
	 * @var int
	 */
	private $lastPoint = 0;

	/**
	 * Plugin for MultiEditor
	 * @var RunalyzePluginTool_MultiEditor
	 */
	protected $MultiEditor = null;

	/**
	 * Save sent TCX-data as file 
	 * @param string $fileName extension ".tcx" is added automatically
	 * @param string $content
	 */
	static public function saveTCX($fileName, $content) {
		Filesystem::writeFile('import/files/'.$fileName.'.tcx', $content);
	}

	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		if (isset($_POST['data']) && $_POST['data'] == 'FINISHED' && is_array($_POST['activityIds'])) {
			$this->createTrainingsFromFiles($_POST['activityIds']);
		} else {
			$this->CompleteXML = simplexml_load_string_utf8($this->getFileContentAsString());
			$this->parseXML();
		}
	}

	/**
	 * Overwrite standard method to have own formular
	 */
	public function displayHTMLformular() {
		if (!is_null($this->MultiEditor)) {
			$this->MultiEditor->showImportedMessage();
			$this->MultiEditor->display();
		} else {
			parent::displayHTMLformular();
		}
	}

	/**
	 * Create all trainings from given files
	 * @param array $fileNames
	 */
	protected function createTrainingsFromFiles($fileNames) {
		$IDs   = array();
		$_POST = array();

		foreach ($fileNames as $fileName) {
			$rawFileContent    = Filesystem::openFileAndDelete('import/files/'.$fileName.'.tcx');
			$this->CompleteXML = simplexml_load_string_utf8( ImporterTCX::decodeCompressedData($rawFileContent) );
			$this->parseXML();
			$this->transformTrainingDataToPostData();

			$Importer = new ImporterFormular();
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
		if (!$this->isGarminFile())
			return;

		$multipleFiles = (count($this->CompleteXML->Activities->Activity) > 1);
		$IDs = array();

		foreach ($this->CompleteXML->Activities->Activity as $Activity) {
			$this->initParser($Activity);
			$this->parseStarttime();
			$this->parseLaps();
			$this->setValues();

			if ($multipleFiles) {
				$this->transformTrainingDataToPostData();

				$Importer = new ImporterFormular();
				$Importer->setTrainingValues();
				$Importer->parsePostData();
				$Importer->insertTraining();

				$IDs[] = $Importer->insertedID;
			}
		}

		if ($multipleFiles)
			$this->forwardToMultiEditor($IDs);
	}

	/**
	 * Set all parsed values
	 */
	protected function setValues() {
		$this->setGeneralValues();
		$this->setOptionalValue();
		$this->setAllArrays();
	}

	/**
	 * Set general values
	 */
	protected function setGeneralValues() {
		$this->set('sportid', CONF_RUNNINGSPORT);
		$this->set('kcal', $this->calories);
		$this->set('splits', implode('-', $this->data['splits']));
	}

	/**
	 * Set optional values
	 */
	protected function setOptionalValue() {
		if (!empty($this->data['distance']))
			$this->set('distance', round(end($this->data['distance']), 2));
		elseif ($this->data['laps_distance'] > 0)
			$this->set('distance', round($this->data['laps_distance'], 2));

		if (!empty($this->data['time']))
			$this->set('s', end($this->data['time']));
		elseif ($this->data['laps_time'] > 0)
			$this->set('s', $this->data['laps_time']);

		if (!empty($this->data['heartrate'])) {
			$this->set('pulse_avg', round(array_sum($this->data['heartrate'])/count($this->data['heartrate'])));
			$this->set('pulse_max', max($this->data['heartrate']));
		}

		if (!empty($this->XML->Training))
			$this->set('comment', (string)$this->XML->Training->Plan->Name);
	}

	/**
	 * Set all arrays
	 */
	protected function setAllArrays() {
		$this->setArrayForTime($this->data['time']);
		$this->setArrayForLatitude($this->data['latitude']);
		$this->setArrayForLongitude($this->data['longitude']);
		$this->setArrayForElevation($this->data['altitude']);
		$this->setArrayForDistance($this->data['distance']);
		$this->setArrayForHeartrate($this->data['heartrate']);
		$this->setArrayForPace($this->data['pace']);
	}

	/**
	 * Init the parser
	 * @param SimpleXmlElement $CurrentActivity
	 */
	protected function initParser($CurrentActivity) {
		$this->XML = $CurrentActivity;
		$this->initEmptyValues();
	}

	/**
	 * Init all empty values 
	 */
	protected function initEmptyValues() {
		$this->starttime = 0;
		$this->calories  = 0;
		$this->data      = array(
			'laps_distance' => 0,
			'laps_time'     => 0,
			'time'          => array(),
			'latitude'      => array(),
			'longitude'     => array(),
			'altitude'      => array(),
			'distance'      => array(),
			'heartrate'     => array(),
			'pace'          => array(),
			'splits'        => array());
	}

	/**
	 * Parse starttime
	 */
	protected function parseStarttime() {
		$this->starttime = strtotime((string)$this->XML->Id);

		$this->set('time', $this->starttime);
		$this->set('datum', date("d.m.Y", $this->starttime));
		$this->set('zeit', date("H:i", $this->starttime));
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		foreach ($this->XML->Lap as $Lap)
			$this->parseLap($Lap);
	}

	/**
	 * Parse one single lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseLap($Lap) {
		$this->parseLapValues($Lap);
		$this->parseTrackpoints($Lap);
	}

	/**
	 * Parse general lap-values
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseLapValues($Lap) {
		if (!empty($Lap->Calories))
			$this->calories += (int)$Lap->Calories;

		$this->data['laps_distance'] += round((int)$Lap->DistanceMeters)/1000;
		$this->data['laps_time']     += round((int)$Lap->TotalTimeSeconds);

		if ((string)$Lap->Intensity == 'Active')
			$this->data['splits'][] = round((int)$Lap->DistanceMeters/1000, 2).'|'.Helper::Time(round((int)$Lap->TotalTimeSeconds), false, 2);
	}

	/**
	 * Parse all trackpoints for one lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseTrackpoints($Lap) {
		$this->lastPoint = 0;

		foreach ($Lap->Track as $Track)
			foreach ($Track->Trackpoint as $Trackpoint)
				$this->parseTrackpoint($Trackpoint);
	}

	/**
	 * Parse one trackpoint
	 * @param SimpleXMLElement $TP
	 */
	protected function parseTrackpoint($TP) {
		// TODO:
		// Just check for empty($TP->DistanceMeters)
		// Other check was kind of Auto-Pause - but this behavior is NOT the expected one of Runalyze
		// -> Verify these thoughts with Unittests!
		//if (empty($TP->DistanceMeters) || ((int)$TP->DistanceMeters <= $this->lastPoint && (int)$TP->DistanceMeters > 0)) {
		if (empty($TP->DistanceMeters)) {
			$this->starttime = strtotime((string)$TP->Time) - end($this->data['time']);
			return;
		}

		$this->lastPoint           = (int)$TP->DistanceMeters;
		$this->data['time'][]      = strtotime((string)$TP->Time) - $this->starttime;
		$this->data['distance'][]  = round((int)$TP->DistanceMeters)/1000;
		$this->data['altitude'][]  = (int)$TP->AltitudeMeters;
		$this->data['pace'][]      = ((end($this->data['distance']) - prev($this->data['distance'])) != 0)
									? round((end($this->data['time']) - prev($this->data['time'])) / (end($this->data['distance']) - prev($this->data['distance'])))
									: 0;
		$this->data['heartrate'][] = (!empty($TP->HeartRateBpm))
									? round($TP->HeartRateBpm->Value)
									: 0;

		if (!empty($TP->Position)) {
			$this->data['latitude'][]  = (double)$TP->Position->LatitudeDegrees;
			$this->data['longitude'][] = (double)$TP->Position->LongitudeDegrees;
		} elseif (!empty($this->data['latitude'])) {
			$this->data['latitude'][]  = end($this->data['latitude']);
			$this->data['longitude'][] = end($this->data['longitude']);
		} else {
			$this->data['latitude'][]  = 0;
			$this->data['longitude'][] = 0;
		}
	}

	/**
	 * Is the given file an garmin-TCX-file?
	 * @return bool
	 */
	private function isGarminFile() {
		if (!empty($this->CompleteXML->Activities->Activity))
			return true;

		if (!empty($this->CompleteXML->Courses))
			$this->addError('Dies ist eine Garmin Course TCX-Datei und enth&auml;lt keine Trainingsdaten.');
		else
			$this->addError('Es scheint keine Garmin-Trainingsdatei zu sein.');

		return false;
	}

	/**
	 * Add information from temporary file to existing training
	 * @param int $id
	 * @param string $tempFileName
	 */
	public static function addTCXdataToTraining($id, $tempFileName) {
		$Training = new Training($id);
		$Importer = Importer::getInstance($tempFileName);
		$Data     = array();
		$Vars     = array();
		
		if ($Training->get('elevation') == 0)
			$Vars[] = 'elevation';
		
		$Vars[] = 'arr_time';
		$Vars[] = 'arr_lat';
		$Vars[] = 'arr_lon';
		$Vars[] = 'arr_alt';
		$Vars[] = 'arr_dist';
		$Vars[] = 'arr_heart';
		$Vars[] = 'arr_pace';
		
		if ($Training->get('pulse_avg') == 0 && $Training->get('pulse_max') == 0) {
			$Vars[] = 'pulse_avg';
			$Vars[] = 'pulse_max';
		}
			
		if ($Training->Sport()->hasTypes() && $Training->Type()->hasSplits() && strlen($Training->get('splits')) == 0)
			$Vars[] = 'splits';
		
		foreach ($Vars as $var)
			$Data[$var] = $Importer->get($var);
		
		$Editor = new Editor($id, $Data);
		$Editor->performUpdate();
		
		$Errors = $Editor->getErrorsAsArray();
		if (!empty($Errors))
			echo HTML::error(implode('<br />', $Errors));
	}

	/**
	 * Decode from Garmin-Communicator compressed data (base64, gzip)
	 * @param string $string
	 * @return string
	 */
	static public function decodeCompressedData($string) {
		$string = mb_substr($string, mb_strpos($string, "\n") + 1);
		return gzinflate(substr(base64_decode($string),10,-8));
	}
}