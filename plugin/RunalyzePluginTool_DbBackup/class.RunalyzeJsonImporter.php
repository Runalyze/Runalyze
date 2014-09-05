<?php
/**
 * This file contains class::RunalyzeJsonImporter
 * @package Runalyze\Plugins\Tools
 */
if (!function_exists('gzdecode')) {
	/**
	 * gzdecode for PHP <= 5.2
	 * @param string $data
	 * @return string
	 */
	function gzdecode($data) {
		return gzinflate(substr($data,10,-8)); 
	}
}

/**
 * Class: RunalyzeJsonImporter
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
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
	 * DB object
	 * @var PDOforRunalyze
	 */
	protected $DB = null;

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
		$this->DB       = DB::getInstance();

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
		if (!empty($this->Errors)) {
			Filesystem::deleteFile($this->filename);
			return;
		}

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
		$DesiredTables = array(
		//	'runalyze_account',
			'runalyze_clothes',
			'runalyze_conf',
			'runalyze_dataset',
			'runalyze_plugin',
		//	'runalyze_shoe',
			'runalyze_sport',
			'runalyze_training',
			'runalyze_type',
		//	'runalyze_user'
		);

		foreach ($DesiredTables as $Table)
			if (!array_key_exists($Table, $this->Data))
				$this->Errors[] = __('The table "'.$Table.'" does not exist.');

		// "<" and ">" are transformed directly while inserting
	}

	/**
	 * Correct table names for a different prefix 
	 */
	private function correctTableNames() {
		// Nothing to do, DbBackup changes tablenames when saving as json
	}

	/**
	 * Delete all old data (if wanted) 
	 */
	private function deleteOldData() {
		if ($this->deleteOldTrainings)
			$this->DB->query('DELETE FROM `'.PREFIX.'training`');

		if ($this->deleteOldUserData)
			$this->DB->query('DELETE FROM `'.PREFIX.'user`');

		if ($this->deleteOldShoes)
			$this->DB->query('DELETE FROM `'.PREFIX.'shoe`');
	}

	/**
	 * Import general tables and set ReplaceIDs 
	 */
	private function importGeneralTables() {
		if ($this->overwriteConfig) {
			$this->DB->query('DELETE FROM `'.PREFIX.'conf`');
			$this->importCompleteTable(PREFIX.'conf');
		}

		if ($this->overwriteDataset) {
			$this->DB->query('DELETE FROM `'.PREFIX.'dataset`');
			$this->importCompleteTable(PREFIX.'dataset');
		}

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

		if (!isset($this->Data[$tablename]) || !is_array($this->Data[$tablename]))
			return;

		foreach ($this->Data[$tablename] as $row) {
			if ($replaceIDs) {
				$row['name']  = isset($row['name']) ? DB::getInstance()->escape($row['name']) : '""';
				$ExistingData = $this->DB->query('SELECT id FROM `'.$tablename.'` WHERE `name`='.$row['name'].' LIMIT 1')->fetch();
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
						$values[]  = HTML::codeTransform($value);
					}
				}

				$newID = $this->DB->insert($tablename, $columns, $values);
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
				$values[]  = HTML::codeTransform($value);
			}
		}

		$this->DB->insert('training', $columns, $values);
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

		if (!is_array($IDs))
			return $String;

		foreach ($IDs as $i => $ID)
			if ((int)$ID > 0 && isset($this->ReplaceIDs[PREFIX.'clothes'][$ID]))
				$IDs[$i] = $this->ReplaceIDs[PREFIX.'clothes'][$ID];

		return implode(',', $IDs);
	}

	/**
	 * Correct config settings 
	 */
	private function correctConfigSettings() {
		if ($this->overwriteConfig) {
			// TODO
			// Configuration::loadAll();
			// ConfigurationValueSelectDB::getAllValues();
			$ConfigValues = ConfigValueSelectDb::getAllValues();
			foreach ($ConfigValues as $key => $Options) {
				$Options['table'] = PREFIX.$Options['table'];

				if (isset($this->ReplaceIDs[$Options['table']])) {
					$InsertedData = $this->DB->query('SELECT `value` FROM `'.PREFIX.'conf` WHERE `key`="'.$key.'" LIMIT 1')->fetch();
					if (isset($InsertedData['value']) && isset($this->ReplaceIDs[$Options['table']][$InsertedData['value']]))
						ConfigValue::update($key, $this->ReplaceIDs[$Options['table']][$InsertedData['value']]);
				}
			}
		}

		if ($this->overwritePluginConf) {
			foreach ($this->Data[PREFIX.'plugin'] as $Plugin) {
				$this->DB->query('
					UPDATE `'.PREFIX.'plugin`
					SET
						`config`='.DB::getInstance()->escape($Plugin['config']).',
						`internal_data`='.DB::getInstance()->escape($Plugin['internal_data']).'
					WHERE
						`key`='.DB::getInstance()->escape($Plugin['key']).'
				');
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