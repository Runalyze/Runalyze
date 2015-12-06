<?php
/**
 * This file contains the class::RunalyzeBackup
 * @package Runalyze\Plugins\Tools
 */

use Runalyze\Util\File\GZipWriter;

/**
 * RunalyzeBackup
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
abstract class RunalyzeBackup {
	/**
	 * Writer
	 * @var \Runalyze\Util\File\GZipWriter
	 */
	protected $Writer;

	/**
	 * DB
	 * @var PDOforRunalyze
	 */
	protected $DB;

	/**
	 * Account ID
	 * @var int
	 */
	protected $AccountID;

	/**
	 * Construct
	 * @param string $fileName
	 */
	public function __construct($fileName) {
		$this->Writer = new GZipWriter(FRONTEND_PATH.$fileName);
		$this->DB = DB::getInstance();
		$this->AccountID = SessionAccountHandler::getId();
	}

	/**
	 * Run backup
	 */
	final public function run() {
		$this->DB->stopAddingAccountID();

		$Tables = array(
			PREFIX.'account',
			PREFIX.'conf',
			PREFIX.'dataset',
			PREFIX.'hrv',
			PREFIX.'plugin',
			PREFIX.'plugin_conf',
			PREFIX.'sport',
			PREFIX.'type',
			PREFIX.'user',
			PREFIX.'route',
			PREFIX.'training',
			PREFIX.'trackdata',
			PREFIX.'swimdata',
			PREFIX.'equipment_type',
			PREFIX.'equipment_sport',
			PREFIX.'equipment',
			PREFIX.'activity_equipment'
		);

		foreach ($Tables as $TableName) {
			$this->saveTableStructure($TableName);
			$this->saveTableRows($TableName);
		}

		$this->Writer->finish();

		$this->DB->startAddingAccountID();
	}

	/**
	 * Save table structure
	 * @param string $TableName
	 */
	abstract protected function saveTableStructure($TableName);

	/**
	 * Save table rows
	 * @param string $TableName
	 */
	private function saveTableRows($TableName) {
		$ColumnInfo = $this->DB->query('SHOW COLUMNS FROM '.$TableName)->fetchAll();

		$Query = 'SELECT * FROM `'.$TableName.'`';
		$ids = $this->addConditionToQuery($Query, $TableName);

		$this->startTableRows($TableName);

		if (!is_array($ids) || !empty($ids)) {
			$Statement = $this->DB->query($Query);
			$this->saveRowsFromStatement($TableName, $ColumnInfo, $Statement);
		}

		$this->finishTableRows();
	}
	/**
	 * @param string $query
	 * @param string $tableName
	 * @return boole|array
	 */
	private function addConditionToQuery(&$query, $tableName) {
		$ids = false;
		if ($tableName == PREFIX.'account') {
			$query .= ' WHERE `id`='.$this->AccountID.' LIMIT 1';
		} elseif ($tableName == PREFIX.'plugin_conf') {
			$ids = $this->fetchPluginIDs();
			$query .= ' WHERE `pluginid` IN('.implode(',', $ids).')';
		} elseif ($tableName == PREFIX.'equipment_sport') {
			$ids = $this->fetchEquipmentTypeIDs();
			$query .= ' WHERE `equipment_typeid` IN('.implode(',', $ids).')';
		} elseif ($tableName == PREFIX.'activity_equipment') {
			$ids = $this->fetchEquipmentIDs();
			$query .= ' WHERE `equipmentid` IN('.implode(',', $ids).')';
		} else {
			$query .= ' WHERE `accountid`='.$this->AccountID;
		}
		return $ids;
	}

	/**
	 * Start table rows
	 * @param string $TableName
	 */
	protected function startTableRows($TableName) {}

	/**
	 * Finish table rows
	 */
	protected function finishTableRows() {}

	/**
	 * Plugin IDs
	 * @return array
	 */
	private function fetchPluginIDs() {
		return $this->DB->query('SELECT `id` FROM `'.PREFIX.'plugin` WHERE `accountid`='.$this->AccountID)->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * Equipment type IDs
	 * @return array
	 */
	private function fetchEquipmentIDs() {
		return $this->DB->query('SELECT `id` FROM `'.PREFIX.'equipment` WHERE `accountid`='.$this->AccountID)->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * Equipment type IDs
	 * @return array
	 */
	private function fetchEquipmentTypeIDs() {
		return $this->DB->query('SELECT `id` FROM `'.PREFIX.'equipment_type` WHERE `accountid`='.$this->AccountID)->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * Save rows from statement
	 * @param string $TableName
	 * @param array $ColumnInfo
	 * @param PDOStatement $Statement
	 */
	abstract protected function saveRowsFromStatement(&$TableName, array $ColumnInfo, PDOStatement $Statement);
}