<?php
/**
 * This file contains class::RunalyzeJsonImporter
 * @package Runalyze\Plugins\Tools
 */

use Runalyze\Configuration;
use Runalyze\Util\File\GZipReader;

/**
 * RunalyzeJsonImporter
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzeJsonImporter {
	/**
	 * Reader
	 * @var \Runalyze\Util\File\GZipReader
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
	 * @param int $accountID optional, session account id is used otherwise
	 */
	public function __construct($fileName, $accountID = false) {
		$this->Reader = new GZipReader(FRONTEND_PATH.$fileName);
		$this->DB = DB::getInstance();
		$this->AccountID = SessionAccountHandler::getId();
		$this->Results = new RunalyzeJsonImporterResults();

		if ($accountID !== false) {
			$this->AccountID = $accountID;
		}
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
			'delete_trainings'	=> array('training', 'route'),
			'delete_user_data'	=> array('user'),
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
		$this->Results->addDeletes('runalyze_'.$table, $this->DB->query('DELETE FROM `'.PREFIX.$table.'` WHERE `accountid`="'.$this->AccountID.'"')->rowCount());
	}

	/**
	 * Read existing data
	 */
	private function readExistingData() {
		Configuration::loadAll();

		$Tables = array(
			'sport'		=> 'name',
			'type'		=> 'name',
			'plugin'	=> 'key',
			'equipment'	=> 'name',
			'equipment_type' => 'name'
		);

		foreach ($Tables as $Table => $Column) {
			$this->ExistingData['runalyze_'.$Table] = array();
			$Statement = $this->DB->query('SELECT `id`,`'.$Column.'` FROM `'.PREFIX.$Table.'`');

			while ($Row = $Statement->fetch()) {
				$this->ExistingData['runalyze_'.$Table][$Row[$Column]] = $Row['id'];
			}
		}

		$FetchEquipmentSportRelation = $this->DB->query('SELECT CONCAT(`sportid`, "-", `equipment_typeid`) FROM `'.PREFIX.'equipment_sport`');

		while ($Relation = $FetchEquipmentSportRelation->fetchColumn()) {
			$this->ExistingData['runalyze_equipment_sport'][] = $Relation;
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
				'runalyze_sport',
				'runalyze_type',
				'runalyze_user',
				'runalyze_equipment_type',
				'runalyze_equipment_sport',
				'runalyze_equipment',
				'runalyze_route',
				'runalyze_training',
				'runalyze_swimdata',
				'runalyze_trackdata',
				'runalyze_hrv',
				'runalyze_activity_equipment'
			),
			'update'	=> array(
				'runalyze_conf'			=> 'overwrite_config',
				'runalyze_dataset'		=> 'overwrite_dataset',
				'runalyze_plugin'		=> 'overwrite_plugin',
				'runalyze_plugin_conf'	=> 'overwrite_plugin'
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
			case 'runalyze_conf':
				return $this->DB->prepare('UPDATE `'.PREFIX.'conf` SET `value`=? WHERE `accountid`='.$this->AccountID.' AND `key`=?');

			case 'runalyze_dataset':
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

			case 'runalyze_plugin':
				return $this->DB->prepare('UPDATE `'.PREFIX.'plugin` SET `active`=?, `order`=? WHERE `accountid`='.$this->AccountID.' AND `key`=?');

			case 'runalyze_plugin_conf':
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
			case 'runalyze_conf':
				$Statement->execute(array($Row['value'], $Row['key']));
				break;

			case 'runalyze_dataset':
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

			case 'runalyze_plugin':
				if (isset($this->ExistingData['runalyze_plugin'][$Row['key']])) {
					$this->ExistingData['runalyze_plugin'][$ID] = $this->ExistingData['runalyze_plugin'][$Row['key']];
				}

				$Statement->execute(array($Row['active'], $Row['order'], $Row['key']));
				break;

			case 'runalyze_plugin_conf':
				$Statement->execute(array($Row['value'], $this->ExistingData['runalyze_plugin'][$Row['pluginid']], $Row['config']));
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

			if (in_array($TableName, array(
				'runalyze_equipment',
				'runalyze_equipment_type',
				'runalyze_plugin',
				'runalyze_route',
				'runalyze_sport',
				'runalyze_type'
			))) {
				if (isset($this->ExistingData[$TableName][$Values[0]])) {
					$this->ReplaceIDs[$TableName][$ID] = $this->ExistingData[$TableName][$Values[0]];
				} else {
					$this->correctValues($TableName, $Row);

					$this->ReplaceIDs[$TableName][$ID] = $BulkInsert->insert(array_values($Row));
					$this->Results->addInserts($TableName, 1);
				}
			} elseif (
				$TableName == 'runalyze_equipment_sport' &&
				$this->equipmentSportRelationDoesExist($Row['sportid'], $Row['equipment_typeid'])
			) {
				// Hint: Don't insert this relation, it does exist already!
			} else {
				$this->correctValues($TableName, $Row);

				if ($TableName == 'runalyze_training') {
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
	 * @param int $sportid
	 * @param int $equipmentTypeid
	 * @return boolean
	 */
	protected function equipmentSportRelationDoesExist($sportid, $equipmentTypeid) {
		if (
			isset($this->ReplaceIDs['runalyze_sport'][$sportid]) &&
			isset($this->ReplaceIDs['runalyze_equipment_type'][$equipmentTypeid]) &&
			in_array(
				$this->ReplaceIDs['runalyze_sport'][$sportid].'-'.$this->ReplaceIDs['runalyze_equipment_type'][$equipmentTypeid],
				$this->ExistingData['runalyze_equipment_sport']
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Correct values
	 * @param string $TableName
	 * @param array $Row
	 */
	private function correctValues($TableName, array &$Row) {
		if ($TableName == 'runalyze_training') {
			$this->correctTraining($Row);
		} elseif ($TableName == 'runalyze_plugin_conf') {
			$Row['pluginid'] = $this->correctID('runalyze_plugin', $Row['pluginid']);
		} elseif ($TableName == 'runalyze_trackdata') {
			$Row['activityid'] = $this->correctID('runalyze_training', $Row['activityid']);
		} elseif ($TableName == 'runalyze_swimdata') {
			$Row['activityid'] = $this->correctID('runalyze_training', $Row['activityid']);
		} elseif ($TableName == 'runalyze_hrv') {
			$Row['activityid'] = $this->correctID('runalyze_training', $Row['activityid']);
		} elseif ($TableName == 'runalyze_equipment') {
			$Row['typeid'] = $this->correctID('runalyze_equipment_type', $Row['typeid']);
		} elseif ($TableName == 'runalyze_equipment_sport') {
			$Row['sportid'] = $this->correctID('runalyze_sport', $Row['sportid']);
			$Row['equipment_typeid'] = $this->correctID('runalyze_equipment_type', $Row['equipment_typeid']);
		} elseif ($TableName == 'runalyze_activity_equipment') {
			$Row['activityid'] = $this->correctID('runalyze_training', $Row['activityid']);
			$Row['equipmentid'] = $this->correctID('runalyze_equipment', $Row['equipmentid']);
		}
	}

	/**
	 * Correct training
	 * @param array $Training
	 */
	private function correctTraining(array &$Training) {
		if (isset($Training['sportid'])) {
			$Training['sportid'] = $this->correctID('runalyze_sport', $Training['sportid']);
		}

		if (isset($Training['typeid'])) {
			$Training['typeid']  = $this->correctID('runalyze_type', $Training['typeid']);
		}

		if (isset($Training['routeid'])) {
			$Training['routeid'] = $this->correctID('runalyze_route', $Training['routeid']);
		}
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
	 * Correct references in configuration
	 */
	private function correctConfigReferences() {
		if (isset($_POST['overwrite_config'])) {
			$ConfigValues = Configuration\Handle::tableHandles();

			foreach ($ConfigValues as $key => $table) {
				$table = 'runalyze_'.$table;

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
