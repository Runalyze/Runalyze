<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

class JsonBackup extends AbstractBackup
{
	/**
	 * Are already tables in the file?
	 * @var bool
	 */
	protected $TablesStarted = false;

	/**
	 * Start backup
	 */
	protected function startBackup()
    {
		$this->Writer->addToFile('version='.$this->RunalyzeVersion."\n\n");
	}

	/**
	 * Start table rows
	 * @param string $tableName
	 */
	protected function startTableRows($tableName)
    {
		$tableName = str_replace($this->Prefix, 'runalyze_', $tableName);
		$this->Writer->addToFile('{"TABLE":"'.$tableName.'"}'."\n");
	}

	/**
	 * Finish table rows
	 */
	protected function finishTableRows()
    {
		$this->Writer->addToFile("\n");
	}

	/**
	 * Save rows from statement
	 * @param string $tableName
	 * @param array $columnInfo
	 * @param \PDOStatement $statement
	 */
	protected function saveRowsFromStatement(&$tableName, array $columnInfo, \PDOStatement $statement) {
		if ($tableName == $this->Prefix.'account')
			return;

        $tableName = str_replace($this->Prefix, 'runalyze_', $tableName);

		while ($row = $statement->fetch()) {
			$id = isset($row['id']) ? $row['id'] : '';
			unset($row['id']);

			if (isset($row['accountid']))
				unset($row['accountid']);

			$this->Writer->addToFile('{"'.$id.'":'.json_encode($row).'}'."\n");
		}
	}
}
