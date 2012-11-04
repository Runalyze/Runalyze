<?php
if (!function_exists('gzdecode')) {
	function gzdecode($data) {
		return gzinflate(substr($data,10,-8)); 
	}
}

/**
 * Class: RunalyzeJsonImporter
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class RunalyzeJsonImporter {
	/**
	 * Options: overwrite plugin-configuration?
	 * @var boolean
	 */
	protected $overwritePluginConf = true;

	/**
	 * Options: overwrite configuration?
	 * @var boolean
	 */
	protected $overwriteConfig = true;

	/**
	 * Options: overwrite dataset?
	 * @var boolean
	 */
	protected $overwriteDataset = true;

	/**
	 * Options: delete old trainings?
	 * @var boolean
	 */
	protected $deleteOldTrainings = false;

	/**
	 * Options: delete old user data?
	 * @var boolean
	 */
	protected $deleteOldUserData = false;

	/**
	 * Options: delete old shoes?
	 * @var boolean
	 */
	protected $deleteOldShoes = false;

	/**
	 * File to import
	 * @var string
	 */
	protected $filename = '';

	/**
	 * Complete array from JSON
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Mysql object
	 * @var Mysql
	 */
	protected $Mysql = null;

	/**
	 * Array with all IDs to replace
	 * $ReplaceIDs[table][oldID] = newID
	 * @var array
	 */
	protected $ReplaceIDs = array();

	/**
	 * Internal array with errors
	 * @var array
	 */
	protected $Errors = array();

	/**
	 * Construct importer
	 * @param string $filename relative to FRONTEND_PATH
	 */
	public function __construct($filename) {
		$this->filename = $filename;
		$this->Mysql    = Mysql::getInstance();

		$this->initOptions();
		$this->readFile();
	}

	/**
	 * Get errors
	 * @return array
	 */
	public function getErrors() {
		return $this->Errors;
	}

	/**
	 * Import data 
	 */
	public function importData() {
		$this->deleteOldData();
		$this->importGeneralTables();
		$this->importTablesWithConflictingIDs();
		$this->importTrainings();
		$this->correctConfigSettings();

		Filesystem::deleteFile($this->filename);
	}

	/**
	 * Init all options 
	 */
	private function initOptions() {
		$this->overwriteConfig     = isset($_POST['overwrite_config']);
		$this->overwriteDataset    = isset($_POST['overwrite_dataset']);
		$this->overwritePluginConf = isset($_POST['overwrite_plugin_conf']);
		$this->deleteOldTrainings  = isset($_POST['delete_trainings']);
		$this->deleteOldUserData   = isset($_POST['delete_user_data']);
		$this->deleteOldShoes      = isset($_POST['delete_shoes']);
	}

	/**
	 * Read file 
	 */
	private function readFile() {
		$DecodedData = gzdecode(Filesystem::openFile($this->filename));
		$this->Data  = json_decode($DecodedData, true);

		$this->checkData();
		$this->correctTableNames();
	}

	/**
	 * Check data 
	 */
	private function checkData() {
		// TODO: Check data
		// - check for correct table names
		// - check for html inserts etc.
		Error::getInstance()->addTodo('Check given data!');
	}

	/**
	 * Correct table names for a different prefix 
	 */
	private function correctTableNames() {
		Error::getInstance()->addWarning('Der Import klappt bisher nur, falls das gleiche Datenbankpr&auml;fix verwendet wird.');
		Error::getInstance()->addTodo('See warning!');
	}

	/**
	 * Delete all old data (if wanted) 
	 */
	private function deleteOldData() {
		if ($this->deleteOldTrainings)
			$this->Mysql->query('DELETE FROM `'.PREFIX.'training`');

		if ($this->deleteOldUserData)
			$this->Mysql->query('DELETE FROM `'.PREFIX.'user`');

		if ($this->deleteOldShoes)
			$this->Mysql->query('DELETE FROM `'.PREFIX.'shoe`');
	}

	/**
	 * Import general tables and set ReplaceIDs 
	 */
	private function importGeneralTables() {
		if ($this->overwriteConfig)
			$this->importCompleteTable(PREFIX.'conf');

		if ($this->overwriteDataset)
			$this->importCompleteTable(PREFIX.'dataset');

		$this->importCompleteTable(PREFIX.'user');
	}

	/**
	 * Import tables with conflicting IDs 
	 */
	private function importTablesWithConflictingIDs() {
		$this->importTableAndSetIDs(PREFIX.'clothes');
		$this->importTableAndSetIDs(PREFIX.'shoe');
		$this->importTableAndSetIDs(PREFIX.'sport');
		$this->importTableAndSetIDs(PREFIX.'type');
	}

	/**
	 * Import complete table
	 * @param string $tablename 
	 */
	private function importCompleteTable($tablename) {
		$this->importTable($tablename, false);
	}

	/**
	 * Import table (if rows are not existing) and set IDs for replacement
	 * @param string $tablename 
	 */
	private function importTableAndSetIDs($tablename) {
		$this->importTable($tablename, true);
	}

	/**
	 * Import table
	 * @param string $tablename
	 * @param boolean $replaceIDs
	 */
	private function importTable($tablename, $replaceIDs) {
		if ($replaceIDs)
			$this->ReplaceIDs[$tablename] = array();

		foreach ($this->Data[$tablename] as $row) {
			if ($replaceIDs) {
				$row['name']  = isset($row['name']) ? mysql_real_escape_string($row['name']) : '';
				$ExistingData = $this->Mysql->fetchSingle('SELECT id FROM `'.$tablename.'` WHERE `name`="'.$row['name'].'"');
			} else {
				$ExistingData = false;
			}

			if (isset($ExistingData['id'])) {
				$newID = $ExistingData['id'];
			} else {
				$columns = array();
				$values  = array();
				foreach ($row as $column => $value) {
					if ($column != 'accountid' && $column != 'id') {
						$columns[] = $column;
						$values[]  = $value;
					}
				}

				$newID = $this->Mysql->insert($tablename, $columns, $values);
			}

			if ($replaceIDs)
				$this->ReplaceIDs[$tablename][$row['id']] = $newID;
		}
	}

	/**
	 * Import all trainings 
	 */
	private function importTrainings() {
		foreach ($this->Data[PREFIX.'training'] as $Training) {
			$Training['clothes'] = $this->correctClothes($Training['clothes']);
			$Training['sportid'] = $this->correctID(PREFIX.'sport', $Training['sportid']);
			$Training['typeid']  = $this->correctID(PREFIX.'type', $Training['typeid']);
			$Training['shoeid']  = $this->correctID(PREFIX.'shoe', $Training['shoeid']);

			$this->insertTraining($Training);
		}
	}

	/**
	 * Insert training
	 * @param array $Training 
	 */
	private function insertTraining($Training) {
		$columns = array();
		$values  = array();
		foreach ($Training as $column => $value) {
			if ($column != 'accountid' && $column != 'id') {
				$columns[] = $column;
				$values[]  = $value;
			}
		}

		$this->Mysql->insert(PREFIX.'training', $columns, $values);
	}

	/**
	 * Correct ID
	 * @param string $Table
	 * @param int $ID
	 * @return int 
	 */
	private function correctID($Table, $ID) {
		if (isset($this->ReplaceIDs[$Table][$ID]))
			return $this->ReplaceIDs[$Table][$ID];

		return 0;
	}

	/**
	 * Correct string of clothes
	 * @param string $String
	 * @return string
	 */
	private function correctClothes($String) {
		if (!isset($this->ReplaceIDs[PREFIX.'clothes']) || empty($String))
			return $String;

		$IDs = explode(',', $String);

		foreach ($IDs as $i => $ID)
			if ((int)$ID > 0)
				$IDs[$i] = $this->ReplaceIDs[PREFIX.'clothes'][$ID];

		return implode(',', $String);
	}

	/**
	 * Correct config settings 
	 */
	private function correctConfigSettings() {
		if ($this->overwriteConfig) {
			$ConfigValues = ConfigValueSelectDb::getAllValues();
			foreach ($ConfigValues as $key => $Options) {
				if (isset($this->ReplaceIDs[$Options['table']])) {
					$InsertedData = $this->Mysql->fetchSingle('SELECT `value` FROM `'.PREFIX.'conf` WHERE `key`="'.$key.'"');
					if (isset($InsertedData['value']) && isset($this->ReplaceIDs[$Options['table']][$InsertedData['value']]))
						ConfigValue::update($key, $this->ReplaceIDs[$Options['table']][$InsertedData['value']]);
				}
			}
		}

		if ($this->overwritePluginConf) {
			foreach ($this->Data[PREFIX.'plugin'] as $Plugin) {
				$this->Mysql->updateWhere(PREFIX.'plugin', '`key`="'.$Plugin['key'].'"',
						array('config', 'internal_data'),
						array($Plugin['config'], $Plugin['internal_data']));
			}
		}
	}

	/**
	 * Get number of trainings
	 * @return int
	 */
	public function getNumberOfTrainings() {
		return $this->getNumberOfDataFor(PREFIX.'training');
	}

	/**
	 * Get number of user data
	 * @return int
	 */
	public function getNumberOfUserData() {
		return $this->getNumberOfDataFor(PREFIX.'user');
	}

	/**
	 * Get number of shoes
	 * @return int
	 */
	public function getNumberOfShoes() {
		return $this->getNumberOfDataFor(PREFIX.'shoe');
	}

	/**
	 * Get number of data for a given table
	 * @param string $tableName
	 * @return int 
	 */
	private function getNumberOfDataFor($tableName) {
		if (!isset($this->Data[$tableName]))
			return 0;

		return count($this->Data[$tableName]);
	}
}