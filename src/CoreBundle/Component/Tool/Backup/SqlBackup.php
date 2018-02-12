<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

class SqlBackup extends AbstractBackup
{
	protected function startBackup()
    {
		$this->Writer->addToFile('-- RUNALYZE BACKUP'."\n");
		$this->Writer->addToFile('-- '."\n");
		$this->Writer->addToFile('-- Date: '.date('c')."\n");
		$this->Writer->addToFile('-- Version: '.$this->RunalyzeVersion."\n\n");
	}

	/**
	 * @param string $tableName
	 * @param array $columnInfo
	 * @param \PDOStatement $statement
	 */
	protected function saveRowsFromStatement($tableName, array $columnInfo, \PDOStatement $statement)
    {
		while ($row = $statement->fetch(\PDO::FETCH_NUM)) {
			$values = '';

			foreach ($row as $i => $value) {
				if ($i > 0) {
					$values .= ',';
                }

				if (empty($value) && 'YES' == $columnInfo[$i]['Null']) {
                    $values .= 'NULL';
                } else {
                    $values .= '"'.addslashes($value).'"';
                }
			}

			$this->Writer->addToFile('INSERT INTO '.$tableName.' VALUES('.str_replace("\n", "\\n", $values).');'."\n");
		}
	}
}
