<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

use Runalyze\Configuration;
use Runalyze\Util\File\GZipReader;

class JsonImporter
{
	/** @var \Runalyze\Util\File\GZipReader */
	protected $Reader;

	/** @var \PDOforRunalyze */
	protected $DB;

	/** @var int */
	protected $AccountID;

    /** @var string */
    protected $DatabasePrefix;

	/** @var array $ReplaceIDs[table][oldID] = newID */
	protected $ReplaceIDs = array();

	/** @var array */
	protected $ExistingData = array();

    /** @var array */
    protected $InternalSportIds = array();

	/** @var JsonImporterResults */
	protected $Results;

    /** @var bool */
    protected $OverwriteConfig = false;

    /** @var bool */
    protected $OverwriteDataset = false;

    /** @var bool */
    protected $OverwritePlugins = false;

	/**
	 * @param string $fileName absolute path
     * @param \PDO $pdo
	 * @param int $accountID
     * @param string $databasePrefix
	 */
	public function __construct($fileName, \PDO $pdo, $accountID, $databasePrefix)
    {
		$this->Reader = new GZipReader($fileName);
		$this->DB = $pdo;
		$this->AccountID = $accountID;
        $this->DatabasePrefix = $databasePrefix;
		$this->Results = new JsonImporterResults();
	}

	/**
	 * @return string
	 */
	public function resultsAsString()
    {
		return $this->Results->completeString();
	}

    /**
     * @param bool $flag
     */
    public function enableOverwritingConfig($flag = true)
    {
        $this->OverwriteConfig = $flag;
    }

    /**
     * @param bool $flag
     */
    public function enableOverwritingDataset($flag = true)
    {
        $this->OverwriteDataset = $flag;
    }

    /**
     * @param bool $flag
     */
    public function enableOverwritingPlugins($flag = true)
    {
        $this->OverwritePlugins = $flag;
    }

    public function deleteOldActivities()
    {
        $this->truncateTable('training');
        $this->truncateTable('route');
    }

    public function deleteOldBodyValues()
    {
        $this->truncateTable('user');
    }

    public function importData()
    {
        $this->readExistingData();
        $this->readFile();
        $this->correctConfigReferences();

        \Cache::clean();
    }

	/**
	 * @param string $table without prefix
	 */
	private function truncateTable($table)
    {
		$this->Results->addDeletes('runalyze_'.$table, $this->DB->query('DELETE FROM `'.$this->DatabasePrefix.$table.'` WHERE `accountid`="'.$this->AccountID.'"')->rowCount());
	}

	/**
	 * Read existing data
	 */
	private function readExistingData()
    {
		Configuration::loadAll();

		$Tables = array(
			'sport'		=> 'name',
			'type'		=> 'name',
			'plugin'	=> 'key',
			'equipment'	=> 'name',
			'equipment_type' => 'name',
			'tag'		=> 'tag'
		);

		foreach ($Tables as $Table => $Column) {
			$this->ExistingData['runalyze_'.$Table] = array();
			if ($Table == 'sport') {
                $statement = $this->DB->query('SELECT `id`, `internal_sport_id`, `' . $Column . '` FROM `' . $this->DatabasePrefix . $Table . '`');
            } else {
                $statement = $this->DB->query('SELECT `id`,`' . $Column . '` FROM `' . $this->DatabasePrefix . $Table . '`');
            }

			while ($row = $statement->fetch()) {
				$this->ExistingData['runalyze_'.$Table][$row[$Column]] = $row['id'];
				if ($Table == 'sport' && !empty($row['internal_sport_id'])) {
				    $this->InternalSportIds[$row['internal_sport_id']] = $row['id'];
                }
			}
		}

		$FetchEquipmentSportRelation = $this->DB->query('SELECT CONCAT(`sportid`, "-", `equipment_typeid`) FROM `'.$this->DatabasePrefix.'equipment_sport`');
		$this->ExistingData['runalyze_equipment_sport'] = array();

		while ($Relation = $FetchEquipmentSportRelation->fetchColumn()) {
			$this->ExistingData['runalyze_equipment_sport'][] = $Relation;
		}
	}

	/**
	 * Read file
	 */
	private function readFile()
    {
		while (!$this->Reader->eof()) {
			$line = trim($this->Reader->readLine());

			if (substr($line, 0, 8) == '{"TABLE"') {
				$tableName = substr($line, 10, -2);
				$this->readTable($tableName);
			}
		}
	}

	/**
	 * Read table
	 * @param string $tableName
	 */
	private function readTable($tableName)
    {
        $tableSettings = array(
			'import'	=> array(
				'runalyze_sport',
				'runalyze_type',
				'runalyze_user',
				'runalyze_equipment_type',
				'runalyze_equipment_sport',
				'runalyze_equipment',
				'runalyze_route',
				'runalyze_training',
				'runalyze_swimdata',
				'runalyze_trackdata',
				'runalyze_hrv',
				'runalyze_activity_equipment',
				'runalyze_tag',
				'runalyze_activity_tag',
				'runalyze_raceresult'
			),
			'update'	=> array(
				'runalyze_conf'			=> $this->OverwriteConfig,
				'runalyze_dataset'		=> $this->OverwriteDataset,
				'runalyze_plugin'		=> $this->OverwritePlugins,
				'runalyze_plugin_conf'	=> $this->OverwritePlugins
			)
		);

		if (in_array($tableName, $tableSettings['import'])) {
			$this->importTable($tableName);
		} elseif (isset($tableSettings['update'][$tableName]) && $tableSettings['update'][$tableName]) {
            $this->updateTable($tableName);
		}
	}

	/**
	 * Update table
	 * @param string $tableName
	 */
	private function updateTable($tableName)
    {
		$line = $this->Reader->readLine();

		if ($line{0} != '{')
			return;

		$this->DB->beginTransaction();
		$statement = $this->prepareUpdateStatement($tableName);

		while ($line{0} == '{') {
			$completeRow = json_decode($line, true);
			$id = key($completeRow);
			$row = current($completeRow);

			$this->runPreparedStatement($tableName, $statement, $id, $row);

			$line = $this->Reader->readLine();
		}

		$this->DB->commit();
	}

	/**
	 * Prepare update statement
	 * @param string $tableName
	 * @return \PDOStatement
	 */
	private function prepareUpdateStatement($tableName)
    {
		switch ($tableName) {
			case 'runalyze_conf':
				return $this->DB->prepare('UPDATE `'.$this->DatabasePrefix.'conf` SET `value`=? WHERE `accountid`='.$this->AccountID.' AND `key`=?');

			case 'runalyze_dataset':
				return $this->DB->prepare('
						UPDATE `'.$this->DatabasePrefix.'dataset`
						SET
							`active`=?,
							`style`=?,
							`position`=?
						WHERE `accountid`='.$this->AccountID.' AND `keyid`=?');

			case 'runalyze_plugin':
				return $this->DB->prepare('UPDATE `'.$this->DatabasePrefix.'plugin` SET `active`=?, `order`=? WHERE `accountid`='.$this->AccountID.' AND `key`=?');

			case 'runalyze_plugin_conf':
				return $this->DB->prepare('UPDATE `'.$this->DatabasePrefix.'plugin_conf` SET `value`=? WHERE `pluginid`=? AND `config`=?');
		}
	}

	/**
	 * Run prepared statement
	 * @param string $tableName
	 * @param \PDOStatement $statement
	 * @param int $id
	 * @param array $row
	 */
	private function runPreparedStatement($tableName, \PDOStatement $statement, $id, array $row)
    {
		switch ($tableName) {
			case 'runalyze_conf':
				$statement->execute(array($row['value'], $row['key']));
				break;

			case 'runalyze_dataset':
				$statement->execute(array(
					$row['active'],
					$row['style'],
					$row['position'],
					$row['keyid']
				));
				break;

			case 'runalyze_plugin':
				if (isset($this->ExistingData['runalyze_plugin'][$row['key']])) {
					$this->ExistingData['runalyze_plugin'][$id] = $this->ExistingData['runalyze_plugin'][$row['key']];
				}

				$statement->execute(array($row['active'], $row['order'], $row['key']));
				break;

			case 'runalyze_plugin_conf':
				$statement->execute(array($row['value'], $this->ExistingData['runalyze_plugin'][$row['pluginid']], $row['config']));
				break;

			default:
				return;
		}
		$this->Results->addUpdates($tableName, $statement->rowCount());
	}

	/**
	 * Import table
	 * @param string $tableName
	 */
	private function importTable($tableName)
    {
		$line = $this->Reader->readLine();

		if ($line{0} != '{')
			return;

		$completeRow = json_decode($line, true);
		$row = array_shift($completeRow);
		$columns = array_keys($row);

		$bulkInserter = new BulkInserter($tableName, $columns, $this->AccountID, $this->DatabasePrefix);

		while ($line{0} == '{') {
			$completeRow = json_decode($line, true);
			$id = key($completeRow);
			$row = current($completeRow);
			$values = array_values($row);
			$columnIds = array_flip($columns);
			if (in_array($tableName, array(
				'runalyze_equipment',
				'runalyze_equipment_type',
				'runalyze_plugin',
				'runalyze_route',
                'runalyze_type',
                'runalyze_sport',
				'runalyze_tag'
			))) {
			    if ($tableName == 'runalyze_sport' && isset($columnIds['internal_sport_id'])) {
			        $internalId = $values[$columnIds['internal_sport_id']];
			        if ( isset($this->InternalSportIds[$internalId])) {
                        $this->ReplaceIDs[$tableName][$id] = $this->InternalSportIds[$internalId];
                        break;
                    }
                }
                if (isset($this->ExistingData[$tableName][$values[0]])) {
					$this->ReplaceIDs[$tableName][$id] = $this->ExistingData[$tableName][$values[0]];
				} else {
					$this->correctValues($tableName, $row);

					$this->ReplaceIDs[$tableName][$id] = $bulkInserter->insert(array_values($row));
					$this->Results->addInserts($tableName, 1);
				}
            } elseif (
				$tableName == 'runalyze_equipment_sport' &&
				$this->equipmentSportRelationDoesExist($row['sportid'], $row['equipment_typeid'])
			) {
				// Hint: Don't insert this relation, it does exist already!
			} else {
				$this->correctValues($tableName, $row);

				if ($tableName == 'runalyze_training') {
					$this->ReplaceIDs[$tableName][$id] = $bulkInserter->insert(array_values($row));
				} else {
					$bulkInserter->insert(array_values($row));
				}

				$this->Results->addInserts($tableName, 1);
			}

			$line = $this->Reader->readLine();
		}

	}

	/**
	 * @param int $sportid
	 * @param int $equipmentTypeid
	 * @return boolean
	 */
	protected function equipmentSportRelationDoesExist($sportid, $equipmentTypeid)
    {
		if (
			isset($this->ReplaceIDs['runalyze_sport'][$sportid]) &&
			isset($this->ReplaceIDs['runalyze_equipment_type'][$equipmentTypeid]) &&
			in_array(
				$this->ReplaceIDs['runalyze_sport'][$sportid].'-'.$this->ReplaceIDs['runalyze_equipment_type'][$equipmentTypeid],
				$this->ExistingData['runalyze_equipment_sport']
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Correct values
	 * @param string $tableName
	 * @param array $row
	 */
	private function correctValues($tableName, array &$row)
    {
		if ($tableName == 'runalyze_training') {
            $this->correctActivity($row);
        } elseif ($tableName == 'runalyze_sport') {
		    if (isset($row['default_typeid'])) {
                $row['default_typeid'] = null;
            }

            if (isset($row['main_equipmenttypeid'])) {
                $row['main_equipmenttypeid'] = null;
            }
		} elseif ($tableName == 'runalyze_plugin_conf') {
			$row['pluginid'] = $this->correctID('runalyze_plugin', $row['pluginid']);
		} elseif ($tableName == 'runalyze_trackdata') {
			$row['activityid'] = $this->correctID('runalyze_training', $row['activityid']);
		} elseif ($tableName == 'runalyze_swimdata') {
			$row['activityid'] = $this->correctID('runalyze_training', $row['activityid']);
		} elseif ($tableName == 'runalyze_hrv') {
			$row['activityid'] = $this->correctID('runalyze_training', $row['activityid']);
		} elseif ($tableName == 'runalyze_equipment') {
			$row['typeid'] = $this->correctID('runalyze_equipment_type', $row['typeid']);
		} elseif ($tableName == 'runalyze_equipment_sport') {
			$row['sportid'] = $this->correctID('runalyze_sport', $row['sportid']);
			$row['equipment_typeid'] = $this->correctID('runalyze_equipment_type', $row['equipment_typeid']);
		} elseif ($tableName == 'runalyze_activity_equipment') {
			$row['activityid'] = $this->correctID('runalyze_training', $row['activityid']);
			$row['equipmentid'] = $this->correctID('runalyze_equipment', $row['equipmentid']);
		} elseif ($tableName == 'runalyze_activity_tag') {
			$row['activityid'] = $this->correctID('runalyze_training', $row['activityid']);
			$row['tagid'] = $this->correctID('runalyze_tag', $row['tagid']);
		} elseif ($tableName == 'runalyze_raceresult') {
			$row['activity_id'] = $this->correctID('runalyze_training', $row['activity_id']);
		}
	}

	/**
     * @param array $activity
     */
	private function correctActivity(array &$activity)
    {
		if (isset($activity['sportid'])) {
            $activity['sportid'] = $this->correctID('runalyze_sport', $activity['sportid']);
		}

		if (isset($activity['typeid'])) {
            $activity['typeid']  = $this->correctID('runalyze_type', $activity['typeid']);
		}

		if (isset($activity['routeid'])) {
            $activity['routeid'] = $this->correctID('runalyze_route', $activity['routeid']);
		}
	}

	/**
	 * @param string $table
	 * @param int $id
	 * @return int
	 */
	private function correctID($table, $id)
    {
		if (isset($this->ReplaceIDs[$table][$id]))
			return $this->ReplaceIDs[$table][$id];

		return 0;
	}

	/**
	 * Correct references in configuration
	 */
	private function correctConfigReferences()
    {
		if (isset($_POST['overwrite_config'])) {
			$configValues = Configuration\Handle::tableHandles();

			foreach ($configValues as $key => $table) {
				$table = 'runalyze_'.$table;

				if (isset($this->ReplaceIDs[$table])) {
					$oldValue = $this->DB->query('SELECT `value` FROM `'.$this->DatabasePrefix.'conf` WHERE `key`="'.$key.'" LIMIT 1')->fetchColumn();
					$newValue = $this->correctID($table, $oldValue);

					if ($newValue != 0) {
						$this->DB->updateWhere('conf', '`key`="'.$key.'"', 'value', $newValue);
					}
				}
			}
		}
	}
}
