<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

class JsonBackup extends AbstractBackup
{
	/** @var bool */
	protected $TablesStarted = false;

	protected function startBackup()
    {
		$this->Writer->addToFile('version='.$this->RunalyzeVersion."\n\n");
	}

	protected function startTableRows($tableName)
    {
		$tableName = str_replace($this->Prefix, 'runalyze_', $tableName);
		$this->Writer->addToFile('{"TABLE":"'.$tableName.'"}'."\n");
	}

	protected function finishTableRows()
    {
		$this->Writer->addToFile("\n");
	}

	protected function saveRowsFromStatement($tableName, array $columnInfo, \PDOStatement $statement) {
		if ($tableName == $this->Prefix.'account')
			return;

		while ($row = $statement->fetch()) {
			$id = isset($row['id']) ? $row['id'] : '';
			unset($row['id']);

			if (isset($row['accountid'])) {
                unset($row['accountid']);
            }

			$this->Writer->addToFile('{"'.$id.'":'.json_encode($row).'}'."\n");
		}
	}
}
