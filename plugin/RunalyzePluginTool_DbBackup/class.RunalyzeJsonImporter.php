<?php
/**
 * This file contains class::RunalyzeJsonImporter
 * @package Runalyze\Plugins\Tools
 */

use Runalyze\Configuration;

/**
 * RunalyzeJsonImporter
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzeJsonImporter {
	/**
	 * Reader
	 * @var BigFileReaderGZip
	 */
	protected $Reader;

	/**
	 * Bulk insert
	 * @var RunalyzeBulkInsert
	 */
	protected $BulkInsert;

	/**
	 * DB object
	 * @var PDOforRunalyze
	 */
	protected $DB;

	/**
	 * Account ID
	 * @var int
	 */
	protected $AccountID;

	/**
	 * Array with all IDs to replace
	 * $ReplaceIDs[table][oldID] = newID
	 * @var array
	 */
	protected $ReplaceIDs = array();

	/**
	 * Existing data
	 * @var array
	 */
	protected $ExistingData = array();

	/**
	 * Results
	 * @var RunalyzeJsonImporterResults
	 */
	protected $Results;

	/**
	 * Construct importer
	 * @param string $fileName relative to FRONTEND_PATH
	 */
	public function __construct($fileName) {
		$this->Reader = new BigFileReaderGZip($fileName);
		$this->DB = DB::getInstance();
		$this->AccountID = USER_MUST_LOGIN ? SessionAccountHandler::getId() : 0;
		$this->Results = new RunalyzeJsonImporterResults();
	}

	/**
	 * Results as string
	 * @return string
	 */
	public function resultsAsString() {
		return $this->Results->completeString();
	}

	/**
	 * Import data 
	 */
	public function importData() {
		$this->deleteOldData();
		$this->readExistingData();
		$this->readFile();
		$this->correctConfigReferences();

		System::clearCache();
	}

	/**
	 * Delete all old data (if wanted) 
	 */
	private function deleteOldData() {
		$Requests = array(
			'delete_trainings'	=> array('training', 'route', 'trackdata'),
			'delete_user_data'	=> array('user'),
			'delete_shoes'		=> array('shoe')
		);

		foreach ($Requests as $key => $tables) {
			if (isset($_POST[$key])) {
				foreach ($tables as $table) {
					$this->truncateTable($table);
				}
			}
		}
	}

	/**
	 * Truncate table
	 * @param string $table without prefix
	 */
	private function truncateTable($table) {
		$this->Results->addDeletes(PREFIX.$table, $this->DB->query('DELETE FROM `'.PREFIX.$table.'`')->rowCount());
	}

	/**
	 * Read existing data
	 */
	private function readExistingData() {
		Configuration::loadAll();

		$Tables = array(
			'clothes'	=> 'name',
			'shoe'		=> 'name',
			'sport'		=> 'name',
			'type'		=> 'name',
			'plugin'	=> 'key'
		);

		foreach ($Tables as $Table => $Column) {
			$this->ExistingData[''.$Table] = array();
			$Statement = $this->DB->query('SELECT `id`,`'.$Column.'` FROM `'.PREFIX.$Table.'`');

			while ($Row = $Statement->fetch()) {
				$this->ExistingData[''.$Table][$Row[$Column]] = $Row['id'];
			}
		}
	}

	/**
	 * Read file
	 */
	private function readFile() {
		while (!$this->Reader->eof()) {
			$Line = trim($this->Reader->readLine());

			if (substr($Line, 0, 8) == '{"TABLE"') {
				$TableName = substr($Line, 10, -2);
				$this->readTable($TableName);
			}
		}
	}

	/**
	 * Read table
	 * @param string $TableName
	 */
	private function readTable($TableName) {
		$TableSettings = array(
			'import'	=> array(
				PREFIX.'clothes',
				PREFIX.'shoe',
				PREFIX.'sport',
				PREFIX.'type',
				PREFIX.'user',
				PREFIX.'route',
				PREFIX.'training',
				PREFIX.'trackdata'
			),
			'update'	=> array(
				PREFIX.'conf'			=> 'overwrite_config',
				PREFIX.'dataset'		=> 'overwrite_dataset',
				PREFIX.'plugin'		=> 'overwrite_plugin',
				PREFIX.'plugin_conf'	=> 'overwrite_plugin'
			)
		);

		if (in_array($TableName, $TableSettings['import'])) {
			$this->importTable($TableName);
		} elseif (isset($TableSettings['update'][$TableName])) {
			if (isset($_POST[$TableSettings['update'][$TableName]])) {
				$this->updateTable($TableName);
			}
		}
	}

	/**
	 * Update table
	 * @param string $TableName
	 */
	private function updateTable($TableName) {
		$Line = $this->Reader->readLine();

		if ($Line{0} != '{')
			return;

		$this->DB->beginTransaction();
		$Statement = $this->prepareUpdateStatement($TableName);
		while ($Line{0} == '{') {
			$CompleteRow = json_decode($Line, true);
			$ID = key($CompleteRow);
			$Row = current($CompleteRow);

			$this->runPreparedStatement($TableName, $Statement, $ID, $Row);

			$Line = $this->Reader->readLine();
		}

		$this->DB->commit();
	}

	/**
	 * Prepare update statement
	 * @param string $TableName
	 * @return PDOStatement
	 */
	private function prepareUpdateStatement($TableName) {
		switch ($TableName) {
			case 'conf':
				return $this->DB->prepare('UPDATE `'.PREFIX.'conf` SET `value`=? WHERE `accountid`='.$this->AccountID.' AND `key`=?');

			case 'dataset':
				return $this->DB->prepare('
						UPDATE `'.PREFIX.'dataset`
						SET
							`active`=?,
							`modus`=?,
							`class`=?,
							`style`=?,
							`position`=?,
							`summary`=?
						WHERE `accountid`='.$this->AccountID.' AND `name`=?');

			case 'plugin':
				return $this->DB->prepare('UPDATE `'.PREFIX.'plugin` SET `active`=?, `order`=? WHERE `accountid`='.$this->AccountID.' AND `key`=?');

			case 'plugin_conf':
				return $this->DB->prepare('UPDATE `'.PREFIX.'plugin_conf` SET `value`=? WHERE `pluginid`=? AND `config`=?');
		}
	}

	/**
	 * Run prepared statement
	 * @param string $TableName
	 * @param PDOStatement $Statement
	 * @param int $ID
	 * @param array $Row
	 */
	private function runPreparedStatement($TableName, PDOStatement $Statement, $ID, array $Row) {
		switch ($TableName) {
			case 'conf':
				$Statement->execute(array($Row['value'], $Row['key']));
				break;

			case 'dataset':
				$Statement->execute(array(
					$Row['active'],
					$Row['modus'],
					$Row['class'],
					$Row['style'],
					$Row['position'],
					$Row['summary'],
					$Row['name']
				));
				break;

			case 'plugin':
				if (isset($this->ExistingData['plugin'][$Row['key']])) {
					$this->ExistingData['plugin'][$ID] = $this->ExistingData['plugin'][$Row['key']];
				}

				$Statement->execute(array($Row['active'], $Row['order'], $Row['key']));
				break;

			case 'plugin_conf':
				$Statement->execute(array($Row['value'], $this->ExistingData['plugin'][$Row['pluginid']], $Row['config']));
				break;

			default:
				return;
		}

		$this->Results->addUpdates($TableName, $Statement->rowCount());
	}

	/**
	 * Import table
	 * @param string $TableName
	 */
	private function importTable($TableName) {
		$Line = $this->Reader->readLine();

		if ($Line{0} != '{')
			return;

		$CompleteRow = json_decode($Line, true);
		$Row = array_shift($CompleteRow);
		$Columns = array_keys($Row);

		$BulkInsert = new RunalyzeBulkInsert($TableName, $Columns, $this->AccountID);

		while ($Line{0} == '{') {
			$CompleteRow = json_decode($Line, true);
			$ID = key($CompleteRow);
			$Row = current($CompleteRow);
			$Values = array_values($Row);

			if ($Columns[0] == 'name' || $TableName == 'plugin') {
				if (isset($this->ExistingData[$TableName][$Values[0]])) {
					$this->ReplaceIDs[$TableName][$ID] = $this->ExistingData[$TableName][$Values[0]];
				} else {
					$this->ReplaceIDs[$TableName][$ID] = $BulkInsert->insert($Values);
					$this->Results->addInserts($TableName, 1);
				}
			} else {
				$this->correctValues($TableName, $Row);

				if ($TableName == 'training') {
					$this->ReplaceIDs[$TableName][$ID] = $BulkInsert->insert(array_values($Row));
				} else {
					$BulkInsert->insert(array_values($Row));
				}

				$this->Results->addInserts($TableName, 1);
			}

			$Line = $this->Reader->readLine();
		}
	}

	/**
	 * Correct values
	 * @param string $TableName
	 * @param array $Row
	 */
	private function correctValues($TableName, array &$Row) {
		if ($TableName == 'training') {
			$this->correctTraining($Row);
		} elseif ($TableName == 'plugin_conf') {
			$Row['pluginid'] = $this->correctID('plugin', $Row['pluginid']);
		} elseif ($TableName == 'trackdata') {
			$Row['activityid'] = $this->correctID('training', $Row['activityid']);
		}
	}

	/**
	 * Correct training
	 * @param array $Training
	 */
	private function correctTraining(array &$Training) {
		$Training['clothes'] = $this->correctClothes($Training['clothes']);
		$Training['sportid'] = $this->correctID('sport', $Training['sportid']);
		$Training['typeid']  = $this->correctID('type', $Training['typeid']);
		$Training['shoeid']  = $this->correctID('shoe', $Training['shoeid']);
		$Training['routeid'] = $this->correctID('route', $Training['routeid']);
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
		if (!isset($this->ReplaceIDs['clothes']) || empty($String))
			return $String;

		$IDs = explode(',', $String);

		if (!is_array($IDs))
			return $String;

		foreach ($IDs as $i => $ID)
			if ((int)$ID > 0 && isset($this->ReplaceIDs['clothes'][$ID]))
				$IDs[$i] = $this->ReplaceIDs['clothes'][$ID];

		return implode(',', $IDs);
	}

	/**
	 * Correct references in configuration
	 */
	private function correctConfigReferences() {
		if (isset($_POST['overwrite_config'])) {
			$ConfigValues = Configuration\Handle::tableHandles();

			foreach ($ConfigValues as $key => $table) {
				$table = PREFIX.$table;

				if (isset($this->ReplaceIDs[$table])) {
					$OldValue = $this->DB->query('SELECT `value` FROM `'.PREFIX.'conf` WHERE `key`="'.$key.'" LIMIT 1')->fetchColumn();
					$NewValue = $this->correctID($table, $OldValue);

					if ($NewValue != 0) {
						$this->DB->updateWhere('conf', '`key`="'.$key.'"', 'value', $NewValue);
					}
				}
			}
		}
	}
}