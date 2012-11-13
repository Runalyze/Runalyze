<?php
Importer::addAdditionalInfo('XML-Import: von RunningAHEAD');

/**
 * Class: ImporterXML
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ImporterXML extends Importer {
	/**
	 * Filecontent as string
	 * @var string
	 */
	private $FileContent;

	/**
	 * Array of Training-objects
	 * @var array
	 */
	private $Trainings = array();

	/**
	 * Plugin for MultiEditor
	 * @var Plugin
	 */
	private $MultiEditor = null;

	/**
	 * Boolean flag for having inserted all trainings
	 * @var bool
	 */
	private $inserted = false;

	private $CompleteXML = null;

	private $Routes = array();
	private $FoundSports = array();
	private $FoundTypes = array();
	private $FoundShoes = array();
	private $numTrainings = 0;


	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		if (isset($_POST['xmlFileContent']))
			$this->FileContent = stripslashes($_POST['xmlFileContent']);
		else
			$this->FileContent = $this->getFileContentAsString();

		$this->readFile();
	}

	/**
	 * Overwrite standard method to have own formular
	 */
	public function displayHTMLformular() {
		include 'tpl/tpl.ImporterXML.formular.php';
	}

	/**
	 * Parse xml
	 */
	private function readFile() {
		$this->fileName = SessionAccountHandler::getId().'.'.$this->fileName;
		$this->CompleteXML = simplexml_load_string_utf8($this->FileContent);

		if ($this->CompleteXML == false) {
			Filesystem::throwErrorForBadXml($this->FileContent);
			return false;
		}

		if ($this->CompleteXML->getName() != "RunningAHEADLog") {
			$this->addError('Die XML-Datei muss ein RunningAHEADLog sein.');
			return false;
		}

		if (isset($_POST['insertNow'])) {
			$this->insertAllShoes();
			$this->parseAllTrainings();
			$this->createAllTrainings();
		} else {
			Filesystem::writeFile('import/files/'.$this->fileName, utf8_decode($this->FileContent));

			$this->readSportsAndTypes();
			$this->readShoes();
			$this->numTrainings = count($this->CompleteXML->EventCollection->Event);
		}
	}

	/**
	 * Read sports and types 
	 */
	private function readSportsAndTypes() {
		if (!isset($this->CompleteXML->EventCollection))
			return;

		if (!isset($this->CompleteXML->EventCollection->Event))
			return;

		$Sports = array();
		$Types  = array();

		foreach ($this->CompleteXML->EventCollection->Event as $Event) {
			if (isset($Event['type']) && isset($Event['typeName']) && !in_array($Event['typeName'], $Sports)) {
				$this->FoundSports[] = array(
					'id' => (int)$Event['type'],
					'name' => (string)$Event['typeName']
				);
				$Sports[] = (string)$Event['typeName'];
			}

			if (isset($Event['type']) && isset($Event['typeName']) && !in_array($Event['subtypeName'], $Types)) {
				$this->FoundTypes[] = array(
					'id' => (int)$Event['subtype'],
					'name' => (string)$Event['subtypeName']
				);
				$Types[] = (string)$Event['subtypeName'];
			}
		}
	}

	/**
	 * Read shoes
	 */
	private function readShoes() {
		if (!isset($this->CompleteXML->EquipmentCollection))
			return;

		if (!isset($this->CompleteXML->EquipmentCollection->Equipment))
			return;

		foreach ($this->CompleteXML->EquipmentCollection->Equipment as $Equipment) {
			if (isset($Equipment->Name))
				$this->FoundShoes[] = array(
					'id' => (string)$Equipment['id'],
					'name' => (string)$Equipment->Name
				);
		}
	}

	/**
	 * Insert all shoes 
	 */
	private function insertAllShoes() {
		if (!isset($_POST['shoe']))
			return;

		foreach ($this->CompleteXML->EquipmentCollection->Equipment as $Equipment) {
			$id = (string)$Equipment['id'];

			if (!isset($_POST['shoe'][$id]))
				continue;

			if ($_POST['shoe'][$id] == -1)
				$_POST['shoe'][$id] = 0;
			elseif ($_POST['shoe'][$id] == 0) {
				$_POST['shoe'][$id] = Mysql::getInstance()->insert(PREFIX.'shoe',
						array(
							'name',
							'brand',
							'since',
							'additionalKm',
							'inuse'
						),
						array(
							(string)$Equipment->Name,
							(isset($Equipment->Name['make']) && (string)$Equipment->Name['make'] != 'Unknown') ? (string)$Equipment->Name['make'] : '',
							(isset($Equipment->PurchaseInfo) && isset($Equipment->PurchaseInfo['date'])) ? (string)$Equipment->PurchaseInfo['date'] : '',
							(isset($Equipment->Distance) && isset($Equipment->Distance['initialDistance'])) ? $this->distanceFromUnit($Equipment->Distance['initialDistance'], $Equipment->Distance['unit']) : 0,
							(isset($Equipment->Name['retired']) && (string)$Equipment->Name['retired'] == 'true') ? 0 : 1
					));
			}
		}
	}

	/**
	 * Create all trainings
	 */
	private function createAllTrainings() {
		$IDs = array();

		foreach ($this->Trainings as $Training) {
			$_POST = array();
			$Training->overwritePostArray();
			$this->setCreatorToFileUpload(true);

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
	private function forwardToMultiEditor($IDs) {
		$_GET['ids'] = implode(',', $IDs);
		
		$this->inserted = true;

		if (count($IDs) < 50)
			$this->MultiEditor = Plugin::getInstanceFor('RunalyzePluginTool_MultiEditor');
	}

	/**
	 * Parse all trainings 
	 */
	private function parseAllTrainings() {
		$this->parseAllRoutes();

		foreach ($this->CompleteXML->xpath('EventCollection/Event') as $Event)
			$this->createTrainingFromEvent($Event);
	}

	/**
	 * Parse all routes 
	 */
	private function parseAllRoutes() {
		if (!isset($this->CompleteXML->CourseCollection))
			return;

		foreach ($this->CompleteXML->CourseCollection->Course as $Course) {
			if (isset($Course->Route) && isset($Course->Route->WayPoints)) {
				$Lat  = array();
				$Lon  = array();
				$Alt  = array();
				$Dist = array();

				foreach ($Course->Route->WayPoints->LatLng as $Point) {
					if (empty($Lat))
						$CurrentDist = 0;
					else
						$CurrentDist += GpsData::distance(end($Lat), end($Lon), (string)$Point['lat'], (string)$Point['lng']);

					$Lat[]  = (string)$Point['lat'];
					$Lon[]  = (string)$Point['lng'];
					$Alt[]  = 0;
					$Dist[] = round($CurrentDist,3);
				}

				$this->Routes[(string)$Course['id']] = array(
					'arr_lat' => implode('|', $Lat),
					'arr_lon' => implode('|', $Lon),
					'arr_alt' => implode('|', $Alt),
					'arr_dist' => implode('|', $Dist)
				);
			}
		}
	}

	/**
	 * Create new Training-object
	 * @param SimpleXMLElement $Event
	 */
	private function createTrainingFromEvent($Event) {
		$time    = self::timeFromString((string)$Event['time']);
		$SportID = isset($_POST['sport'][(int)$Event['type']]) ? $_POST['sport'][(int)$Event['type']] : 0;
		$TypeID  = isset($_POST['type'][(int)$Event['subtype']]) ? $_POST['type'][(int)$Event['subtype']] : 0;

		if ($SportID == 0)
			return;

		$Training = new Training(Training::$CONSTRUCTOR_ID);
		$Training->set('time', $time);
		$Training->set('sportid', $SportID);
		$Training->set('typeid', $TypeID);
		$Training->set('use_vdot', 1);

		if (isset($Event->Distance))
			$Training->set('distance', $this->distanceFromUnit((double)$Event->Distance, (string)$Event->Distance['unit']));

		if (isset($Event->Duration)) {
			$Training->set('s', (double)$Event->Duration['seconds']);
			$Training->set('kcal', round(Sport::kcalPerHourFor($SportID)*(double)$Event->Duration['seconds']/3600));
		}

		if (isset($Event->HeartRate)) {
			if (isset($Event->HeartRate->AvgHR))
				$Training->set('pulse_avg', (int)$Event->HeartRate->AvgHR);
			if (isset($Event->HeartRate->MaxHR))
				$Training->set('pulse_max', (int)$Event->HeartRate->MaxHR);
		}

		if (isset($Event->IntervalCollection)) {
			if ((string)$Event->IntervalCollection['name'] != 'GPS Interval')
				$Training->set('comment', utf8_decode((string)$Event->IntervalCollection['name']));

			$Km   = array();
			$Time = array();

			foreach ($Event->IntervalCollection->Interval as $Interval) {
				if ($Interval['typeName'] == 'Interval') {
					$Km[]   = $this->distanceFromUnit((double)$Interval->Distance, (string)$Interval->Distance['unit']);
					$Time[] = round((double)$Interval->Duration['seconds']);
				}
			}

			$Splits = new Splits(array('km' => $Km, 'time' => $Time));
			$Training->set('splits', $Splits->asString());
		}

		if (isset($Event->Equipment) && isset($_POST['shoe'][(string)$Event->Equipment['id']]) && $_POST['shoe'][(string)$Event->Equipment['id']] != -1)
			$Training->set('shoeid', $_POST['shoe'][(string)$Event->Equipment['id']]);

		if (isset($Event->Route)) {
			$Training->set('route', utf8_decode((string)$Event->Route));

			if (isset($this->Routes[(string)$Event->Route['id']])) {
				$LatLon = $this->Routes[(string)$Event->Route['id']];
				$Training->set('arr_lat', $LatLon['arr_lat']);
				$Training->set('arr_lon', $LatLon['arr_lon']);
				$Training->set('arr_alt', $LatLon['arr_alt']);
				$Training->set('arr_dist', $LatLon['arr_dist']);
			}
		}

		$Training->set('weatherid', Weather::$UNKNOWN_ID);

		if (isset($Event->EnvironmentalConditions)) {
			if (isset($Event->EnvironmentalConditions->Temperature)) {
				if ((string)$Event->EnvironmentalConditions->Temperature['unit'] == 'C')
					$Training->set('temperature', (int)$Event->EnvironmentalConditions->Temperature);
				elseif ((string)$Event->EnvironmentalConditions->Temperature['unit'] == 'F')
					$Training->set('temperature', ((int)$Event->EnvironmentalConditions->Temperature - 32) * 5/9 );
			}

			if (isset($Event->EnvironmentalConditions->Conditions)) {
				foreach ($Event->EnvironmentalConditions->Conditions->children() as $Condition) {
					$ID = Weather::getIdFromAPICondition($Condition->getName());
					if ($ID > 0)
						$Training->set('weatherid', $ID);
				}
			}
		}

		if (isset($Event->Notes))
			$Training->set('notes', utf8_decode((string)$Event->Notes));

		$this->Trainings[] = $Training;
	}

	/**
	 * Calculate distance from unit
	 * @param mixed $Distance
	 * @param mixed $Unit
	 * @return double 
	 */
	private function distanceFromUnit($Distance, $Unit) {
		$Distance = (double)$Distance;
		$Unit     = (string)$Unit;

		switch ($Unit) {
			case 'mile':
				return 1.609344*$Distance;
			case 'm':
				return $Distance/1000;
			case 'km':
			default:
				return $Distance;
		}
	}

	/**
	 * Get time from string, correcting wrong UTC
	 * @param string $string
	 * @return int
	 */
	static protected function timeFromString($string) {
		$offset = strlen($string) > 10 ? date('Z', strtotime($string)) : 0;

		return strtotime($string) - $offset;
	}

	/**
	 * Search in database for a string and get the ID
	 * @param string $table
	 * @param string $string
	 * @return int
	 */
	static private function getIDforDatabaseString($table, $string) {
		if ($table == 'type')
			$SearchQuery = 'SELECT id FROM '.PREFIX.$table.' WHERE name LIKE "%'.$string.'%" OR abbr="'.$string.'" LIMIT 1';
		else
			$SearchQuery = 'SELECT id FROM '.PREFIX.$table.' WHERE name LIKE "%'.$string.'%" LIMIT 1';

		$Result = Mysql::getInstance()->fetchSingle($SearchQuery);

		if ($Result === false)
			return 0;

		return $Result['id'];
	}
}
?>