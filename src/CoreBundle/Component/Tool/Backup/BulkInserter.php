<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

class BulkInserter
{
	/** @var \PDOStatement */
	protected $Statement;

	/** @var \PDOforRunalyze */
	protected $DB;

	/**
	 * Start new bulk insert
	 * @param string $tableName
	 * @param array $columns
	 * @param int $accountID
     * @param string $databasePrefix
	 */
	public function __construct($tableName, array $columns, $accountID, $databasePrefix)
    {
		$this->DB = \DB::getInstance();

		$this->prepareStatement($tableName, $columns, $accountID, $databasePrefix);
	}

	/**
	 * Prepare statement
	 * @param string $tableName
	 * @param array $columns
	 * @param int $accountID
     * @param string $databasePrefix
	 */
	protected function prepareStatement($tableName, array $columns, $accountID, $databasePrefix)
    {
		$tableName = str_replace('runalyze_', $databasePrefix, $tableName);
		$PreparedColumns = '`'.implode('`,`', $columns).'`';
		$PreparedValues = implode(',', array_fill(0, count($columns), '?'));

		if (
            $accountID !== false &&
            $tableName != $databasePrefix.'equipment_sport' &&
            $tableName != $databasePrefix.'activity_equipment' &&
            $tableName != $databasePrefix.'activity_tag'
        ) {
			$PreparedColumns .= ',`accountid`';
			$PreparedValues .= ','.$accountID;
		}

		$this->Statement = $this->DB->prepare('INSERT INTO `'.$tableName.'` ('.$PreparedColumns.') VALUES ('.$PreparedValues.')');
	}

	/**
	 * Insert
	 * @param array $values
	 * @return int last inserted ID
	 */
	public function insert(array $values)
    {
		$this->Statement->execute($values);

		return $this->DB->lastInsertId();
	}
}
