<?php
/**
 * This file contains the class::RunalyzeBackup
 * @package Runalyze\Plugins\Tools
 */
/**
 * RunalyzeBackup
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
abstract class RunalyzeBackup {
	/**
	 * Writer
	 * @var BigFileWriterGZip
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
		$this->Writer = new BigFileWriterGZip($fileName);
		$this->DB = DB::getInstance();
		$this->AccountID = USER_MUST_LOGIN ? SessionAccountHandler::getId() : 0;
	}

	/**
	 * Run backup
	 */
	final public function run() {
		$this->DB->stopAddingAccountID();

		$Tables = array(
			PREFIX.'account',
			PREFIX.'clothes',
			PREFIX.'conf',
			PREFIX.'dataset',
			PREFIX.'plugin',
			PREFIX.'plugin_conf',
			PREFIX.'shoe',
			PREFIX.'sport',
			PREFIX.'type',
			PREFIX.'user',
			PREFIX.'route',
			PREFIX.'training',
			PREFIX.'trackdata'
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

		if ($TableName == PREFIX.'account') {
			$Query .= ' WHERE `id`='.$this->AccountID.' LIMIT 1';
		} elseif ($TableName == PREFIX.'plugin_conf') {
			$Query .= ' WHERE `pluginid` IN('.implode(',', $this->fetchPluginIDs()).')';
		} else {
			$Query .= ' WHERE `accountid`='.$this->AccountID;
		}

		$this->startTableRows($TableName);

		$Statement = $this->DB->query($Query);
		$this->saveRowsFromStatement($TableName, $ColumnInfo, $Statement);

		$this->finishTableRows();
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
	 * Save rows from statement
	 * @param string $TableName
	 * @param array $ColumnInfo
	 * @param PDOStatement $Statement
	 */
	abstract protected function saveRowsFromStatement(&$TableName, array $ColumnInfo, PDOStatement $Statement);
}