<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

use Runalyze\Util\File\GZipWriter;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractBackup
{
	/** @var \Runalyze\Util\File\GZipWriter */
	protected $Writer;

    /** @var int */
    protected $AccountID;

	/** @var \PDO  */
	protected $PDO;

    /** @var string */
    protected $Prefix;

    /** @var string */
    protected $RunalyzeVersion;

    /** @var string */
    protected $Filename;

    /**
     * @param string $filename
     * @param int $accountid
     * @param \PDO $databaseConnection
     * @param string $databaseTablePrefix
     * @param string $runalyzeVersion
     */
	public function __construct($filename, $accountid, \PDO $databaseConnection, $databaseTablePrefix, $runalyzeVersion)
    {
		$this->Writer = new GZipWriter(md5($filename));
        $this->AccountID = $accountid;
		$this->PDO = $databaseConnection;
        $this->Prefix = $databaseTablePrefix;
        $this->RunalyzeVersion = $runalyzeVersion;
        $this->Filename = $filename;
	}

	final public function run()
    {
		if ($this->PDO instanceof \PDOforRunalyze) {
			$this->PDO->stopAddingAccountID();
		}

		$this->startBackup();

		// REMINDER: think about required order, e.g.
		// - plugin before plugin_conf
		// - sport/type/route before training
		// - training before trackdata/swimdata/hrv
		// - equipment_Type before equipment_sport before equipment before activity_equipment
		// - tag before activity_tag
		$Tables = array(
			$this->Prefix.'account',
			$this->Prefix.'conf',
			$this->Prefix.'dataset',
			$this->Prefix.'plugin',
			$this->Prefix.'plugin_conf',
			$this->Prefix.'sport',
			$this->Prefix.'type',
			$this->Prefix.'user',
			$this->Prefix.'route',
			$this->Prefix.'training',
			$this->Prefix.'trackdata',
			$this->Prefix.'swimdata',
			$this->Prefix.'hrv',
			$this->Prefix.'equipment_type',
			$this->Prefix.'equipment_sport',
			$this->Prefix.'equipment',
			$this->Prefix.'activity_equipment',
			$this->Prefix.'tag',
			$this->Prefix.'activity_tag',
			$this->Prefix.'raceresult'
		);

		foreach ($Tables as $TableName) {
			$this->saveTableRows($TableName);
		}

		$this->finishBackup();

		if ($this->PDO instanceof \PDOforRunalyze) {
			$this->PDO->startAddingAccountID();
		}

		$this->Writer->finish();
        $fs = new Filesystem();
        $fs->rename(md5($this->Filename), $this->Filename);
    }

	protected function startBackup() {}

	protected function finishBackup() {}

	/**
	 * @param string $tableName
	 */
	private function saveTableRows($tableName)
    {
		$columnInfo = $this->PDO->query('SHOW COLUMNS FROM '.$tableName)->fetchAll();

		$query = 'SELECT * FROM `'.$tableName.'`';
		$ids = $this->addConditionToQuery($query, $tableName);

		$this->startTableRows($tableName);

		if (!is_array($ids) || !empty($ids)) {
			$statement = $this->PDO->query($query);
			$this->saveRowsFromStatement($tableName, $columnInfo, $statement);
		}

		$this->finishTableRows();
	}

	/**
	 * @param string $query
	 * @param string $tableName
	 * @return bool|array
	 */
	private function addConditionToQuery(&$query, $tableName)
    {
		$ids = false;

		if ($tableName == $this->Prefix.'account') {
			$query .= ' WHERE `id`='.$this->AccountID.' LIMIT 1';
		} elseif ($tableName == $this->Prefix.'plugin_conf') {
			$ids = $this->fetchPluginIDs();
			$query .= ' WHERE `pluginid` IN('.implode(',', $ids).')';
		} elseif ($tableName == $this->Prefix.'equipment_sport') {
			$ids = $this->fetchEquipmentTypeIDs();
			$query .= ' WHERE `equipment_typeid` IN('.implode(',', $ids).')';
		} elseif ($tableName == $this->Prefix.'activity_equipment') {
			$ids = $this->fetchEquipmentIDs();
			$query .= ' WHERE `equipmentid` IN('.implode(',', $ids).')';
		} elseif ($tableName == $this->Prefix.'activity_tag') {
			$ids = $this->fetchTagIDs();
			$query .= ' WHERE `tagid` IN('.implode(',', $ids).')';
		} else {
			$query .= ' WHERE `accountid`='.$this->AccountID;
		}

		return $ids;
	}

	/**
	 * @param string $tableName
	 */
	protected function startTableRows($tableName) {}

	protected function finishTableRows() {}

	/**
	 * @return array
	 */
	private function fetchPluginIDs()
    {
		return $this->PDO->query('SELECT `id` FROM `'.$this->Prefix.'plugin` WHERE `accountid`='.$this->AccountID)->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * @return array
	 */
	private function fetchEquipmentIDs()
    {
		return $this->PDO->query('SELECT `id` FROM `'.$this->Prefix.'equipment` WHERE `accountid`='.$this->AccountID)->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * @return array
	 */
	private function fetchTagIDs()
    {
		return $this->PDO->query('SELECT `id` FROM `'.$this->Prefix.'tag` WHERE `accountid`='.$this->AccountID)->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * @return array
	 */
	private function fetchEquipmentTypeIDs()
    {
		return $this->PDO->query('SELECT `id` FROM `'.$this->Prefix.'equipment_type` WHERE `accountid`='.$this->AccountID)->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * @param string $tableName
	 * @param array $columnInfo
	 * @param \PDOStatement $statement
	 */
	abstract protected function saveRowsFromStatement($tableName, array $columnInfo, \PDOStatement $statement);
}
