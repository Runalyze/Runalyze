<?php
/**
 * This file contains the class::RunalyzeBulkInsert
 * @package Runalyze\Plugins\Tools
 */
/**
 * RunalyzeBulkInsert
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzeBulkInsert {
	/**
	 * Statement
	 * @var PDOStatement
	 */
	protected $Statement;

	/**
	 * DB
	 * @var PDOforRunalyze
	 */
	protected $DB;

	/**
	 * Start new bulk insert
	 * @param string $TableName
	 * @param array $Columns
	 * @param int $accountID
	 */
	public function __construct($TableName, array $Columns, $accountID = false) {
		$this->DB = DB::getInstance();

		$this->prepareStatement($TableName, $Columns, $accountID);
	}

	/**
	 * Prepare statement
	 * @param string $TableName
	 * @param array $Columns
	 * @param int $accountID
	 */
	protected function prepareStatement($TableName, array $Columns, $accountID) {
		$TableName = str_replace('runalyze_', PREFIX, $TableName);
		$PreparedColumns = '`'.implode('`,`', $Columns).'`';
		$PreparedValues = implode(',', array_fill(0, count($Columns), '?'));

		if ($accountID !== false) {
			$PreparedColumns .= ',`accountid`';
			$PreparedValues .= ','.$accountID;
		}

		$this->Statement = $this->DB->prepare('INSERT INTO `'.$TableName.'` ('.$PreparedColumns.') VALUES ('.$PreparedValues.')');
	}

	/**
	 * Insert
	 * @param array $Values
	 * @return int last inserted ID
	 */
	public function insert(array $Values) {
		$this->Statement->execute($Values);

		return $this->DB->lastInsertId();
	}
}