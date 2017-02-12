<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

class SqlBackup extends AbstractBackup
{
	/**
	 * Start backup
	 */
	protected function startBackup()
    {
		$this->Writer->addToFile('-- RUNALYZE BACKUP'."\n");
		$this->Writer->addToFile('-- '."\n");
		$this->Writer->addToFile('-- Date: '.date('c')."\n");
		$this->Writer->addToFile('-- Version: '.$this->RunalyzeVersion."\n\n");
	}

	/**
	 * Save rows from statement
	 * @param string $tableName
	 * @param array $columnInfo
	 * @param \PDOStatement $statement
	 */
	protected function saveRowsFromStatement(&$tableName, array $columnInfo, \PDOStatement $statement)
    {
		while ($row = $statement->fetch(\PDO::FETCH_NUM)) {
			$values = '';

			foreach ($row as $i => $value) {
				if ($i > 0)
					$values .= ',';

				if (is_numeric($value))
                    $values .= $value;
				elseif (empty($value) && $columnInfo[$i]['Null'] == 'YES')
                    $values .= 'NULL';
				else
                    $values .= '"'.addslashes($value).'"';
			}

			$this->Writer->addToFile('INSERT INTO '.$tableName.' VALUES('.str_replace("\n", "\\n", $values).');'."\n");
		}
	}
}
