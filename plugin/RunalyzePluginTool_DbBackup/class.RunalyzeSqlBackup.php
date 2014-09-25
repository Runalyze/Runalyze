<?php
/**
 * This file contains the class::RunalyzeSqlBackup
 * @package Runalyze\Plugins\Tools
 */
/**
 * RunalyzeSqlBackup
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzeSqlBackup extends RunalyzeBackup {
	/**
	 * Save table structure
	 * @param string $TableName
	 */
	protected function saveTableStructure($TableName) {
		$CreateResult = $this->DB->query('SHOW CREATE TABLE '.$TableName)->fetchColumn(1);

		$this->Writer->addToFile('DROP TABLE IF EXISTS '.$TableName.';'.NL.NL);
		$this->Writer->addToFile($CreateResult.';'.NL.NL);
	}

	/**
	 * Save rows from statement
	 * @param string $TableName
	 * @param array $ColumnInfo
	 * @param PDOStatement $Statement
	 */
	protected function saveRowsFromStatement(&$TableName, array $ColumnInfo, PDOStatement $Statement) {
		while ($Row = $Statement->fetch(PDO::FETCH_NUM)) {
			$Values = '';

			foreach ($Row as $i => $Value) {
				if ($i > 0)
					$Values .= ',';

				if (is_numeric($Value))
					$Values .= $Value;
				elseif (empty($Value) && $ColumnInfo[$i]['Null'] == 'YES')
					$Values .= 'NULL';
				else
					$Values .= '"'.addslashes($Value).'"';
			}

			$this->Writer->addToFile('INSERT INTO '.$TableName.' VALUES('.str_replace("\n", "\\n", $Values).');'.NL);
		}
	}
}