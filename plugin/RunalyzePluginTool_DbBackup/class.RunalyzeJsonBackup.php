<?php
/**
 * This file contains the class::RunalyzeJsonBackup
 * @package Runalyze\Plugins\Tools
 */
/**
 * RunalyzeJsonBackup
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzeJsonBackup extends RunalyzeBackup {
	/**
	 * Are already tables in the file?
	 * @var bool
	 */
	protected $TablesStarted = false;

	/**
	 * Save table structure
	 * @param string $TableName
	 */
	protected function saveTableStructure($TableName) {
		// No structure saved
	}

	/**
	 * Start table rows
	 * @param string $TableName
	 */
	protected function startTableRows($TableName) {
		$this->Writer->addToFile('{"TABLE":"'.$TableName.'"}'.NL);
	}

	/**
	 * Finish table rows
	 */
	protected function finishTableRows() {
		$this->Writer->addToFile(NL);
	}

	/**
	 * Save rows from statement
	 * @param string $TableName
	 * @param array $ColumnInfo
	 * @param PDOStatement $Statement
	 */
	protected function saveRowsFromStatement(&$TableName, array $ColumnInfo, PDOStatement $Statement) {
		if ($TableName == 'runalyze_account')
			return;

		if (PREFIX != 'runalyze_')
			$TableName = str_replace(PREFIX, 'runalyze_', $TableName);

		while ($Row = $Statement->fetch()) {
			$id = isset($Row['id']) ? $Row['id'] : '';
			unset($Row['id']);

			if (isset($Row['accountid']))
				unset($Row['accountid']);

			$this->Writer->addToFile('{"'.$id.'":'.json_encode($Row).'}'.NL);
		}
	}
}