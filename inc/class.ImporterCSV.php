<?php
/**
 * This file contains the class::ImporterCSV for importing trainings from CSV
 */

Importer::addAdditionalInfo('CSV-Import: Die CSV-Datei sollte pro Zeile ein Training
	und in jeder Spalte wiederkehrend die gleichen Informationen enthalten.
	Im n&auml;chsten Schritt k&ouml;nnen einzelne Trainings abgew&auml;hlt
	und die einzelnen Spalten zugewiesen werden.');

/**
 * Class: ImporterCSV
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 */
class ImporterCSV extends Importer {
	/**
	 * Filecontent as string
	 * @var string
	 */
	private $FileContent;

	/**
	 * Array of Training-objects
	 * @var array
	 */
	private $Trainings;

	/**
	 * Array of rows
	 * @var array
	 */
	private $Rows;

	/**
	 * Number of fields per row
	 * @var int
	 */
	private $RowLength = 0;

	/**
	 * Column names for all rows
	 * @var array
	 */
	private $ColumnNames = array();

	/**
	 * Possible keys for columns
	 * @var array
	 */
	private $PossibleKeys = array();

	/**
	 * Keys to be ignored
	 * @var array
	 */
	private $KeysToIgnore = array();

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

	/**
	 * Separator
	 * @var string
	 */
	static private $SEPARATOR = ';';

	/**
	 * Set all possible keys to import
	 */
	private function setPossibleKeys() {
		$this->PossibleKeys['ignore']      = '-- egal';
		$this->PossibleKeys['datum']       = 'Datum';
		$this->PossibleKeys['zeit']        = 'Uhrzeit';
		$this->PossibleKeys['s']           = 'Dauer';
		$this->PossibleKeys['distance']    = 'Distanz';
		$this->PossibleKeys['kcal']        = 'kcal';
		$this->PossibleKeys['pulse_avg']   = '&oslash;-Puls';
		$this->PossibleKeys['pulse_max']   = 'max-Puls';
		$this->PossibleKeys['comment']     = 'Bemerkung';
		$this->PossibleKeys['partner']     = 'Partner';
		$this->PossibleKeys['route']       = 'Strecke';
		$this->PossibleKeys['elevation']   = 'H&ouml;henmeter';
		$this->PossibleKeys['temperature'] = 'Temperatur';
		$this->PossibleKeys['sportid']     = 'Sportart';
		$this->PossibleKeys['typeid']      = 'Trainingstyp';
		$this->PossibleKeys['shoeid']      = 'Laufschuh';

		$this->KeysToIgnore = array('ignore', 'datum', 'zeit');
	}


	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		$this->setPossibleKeys();

		if (isset($_POST['csvFileContent']))
			$this->FileContent = stripslashes($_POST['csvFileContent']);
		else
			$this->FileContent = $this->getFileContentAsString();

		$this->setRows();
	}

	/**
	 * Overwrite standard method to have own formular
	 */
	public function displayHTMLformular() {
		include 'tpl/tpl.ImporterCSV.formular.php';
	}

	/**
	 * Parse csv and create Training-objects
	 */
	private function setRows() {
		$Data = str_getcsv($this->FileContent, "\n");
		foreach ($Data as &$Row)
			$this->Rows[] = str_getcsv($Row, self::$SEPARATOR);

		$this->setColumnNames();

		if (!empty($this->ColumnNames))
			$this->createAllTrainings();
	}

	/**
	 * Set all column names
	 */
	private function setColumnNames() {
		if (empty($this->Rows))
			return;

		$this->RowLength = count($this->Rows[0]);

		for ($i = 0; $i < $this->RowLength; $i++) {
			if (isset($_POST['key'][$i]))
				$this->ColumnNames[$i] = $_POST['key'][$i];
		}
	}

	/**
	 * Create all trainings
	 */
	private function createAllTrainings() {
		foreach ($this->Rows as $i => $Row) {
			if (isset($_POST['import'][$i]))
				$this->createTrainingFromRow($Row);
		}

		$IDs = array();

		foreach ($this->Trainings as $Training) {
			$_POST = array();
			$Training->overwritePostArray();

			$Importer = Importer::getInstance();
			$Importer->setTrainingValues(false);
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
		$this->MultiEditor = Plugin::getInstanceFor('RunalyzePluginTool_MultiEditor');
	}

	/**
	 * Create new Training-object for a given row
	 * @param array $Row
	 * @return Training
	 */
	private function createTrainingFromRow($Row) {
		$Training = new Training(Training::$CONSTRUCTOR_ID);

		$Sport = new Sport($_POST['sportid']);
		$Training->set('typeid', $_POST['typeid']);
		$Training->set('sportid', $_POST['sportid']);

		$day  = '';
		$time = '00:00';

		foreach ($_POST['key'] as $i => $key) {
			if (!in_array($key, $this->KeysToIgnore))
				$Training->set($key, $this->parseValue($key, $Row[$i]));

			if ($key == 'datum')
				$day  = $Row[$i];
			if ($key == 'zeit')
				$time = $Row[$i];
		}

		$Training->set('time', $this->getTimeFor($day, $time));

		$this->Trainings[] = $Training;
	}

	/**
	 * Transform day and daytime to timestamp
	 * @param string $day
	 * @param string $time
	 * @return int
	 */
	private function getTimeFor($day, $time) {
		$post_day  = explode(".", $day);
		$post_time = explode(":", $time);

		if (count($post_day) < 2)
			$post_day[1] = date("m");

		if (count($post_day) < 3)
			$post_day[2] = isset($_POST['year']) ? $_POST['year'] : date("Y");

		if (count($post_time) < 2)
			$post_time[1] = 0;

		return mktime((int)$post_time[0], (int)$post_time[1], 0, (int)$post_day[1], (int)$post_day[0], (int)$post_day[2]);
	}

	/**
	 * Parse a value
	 * @param string $key
	 * @param string $value
	 * @return string
	 */
	private function parseValue($key, $value) {
		switch ($key) {
			case 's':
				return $value;

			case 'distance':
				$value = Helper::CommaToPoint($value);
			case 'kcal':
			case 'pulse_avg':
			case 'pulse_max':
			case 'elevation':
			case 'temperature':
				return self::getNumericFromString($value);

			case 'sportid':
				$table = 'sport';
				return self::getIDforDatabaseString($table, $value);
			case 'typeid':
				$table = 'type';
				return self::getIDforDatabaseString($table, $value);
			case 'shoeid':
				$table = 'shoe';
				return self::getIDforDatabaseString($table, $value);
	
			case 'datum': // ignore them, 'time' is done later
			case 'zeit':  // ignore them, 'time' is done later
			case 'comment':
			case 'partner':
			case 'route':
			default:
				return $value;
		}
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

		if ($Result === false) {
			if ($table == 'type')
				return $_POST['typeid'];
			if ($table == 'sport')
				return $_POST['sportid'];

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
	static private function getNumericFromString($string, $default = 0) {
		$array  = explode(' ', trim($string));
		$string = preg_replace('/[^0-9\.,]/Uis', '', $array[0]);

		return (double)$string;
	}
}
?>