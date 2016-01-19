<?php
/**
 * This file contains class::Query
 * @package Runalyze
 */

namespace Runalyze\Dataset;

/**
 * Build query to fetch dataset values from `runalyze_training`
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset
 */
class Query
{
	/** @var int */
	const YEAR_TIMERANGE = 31622400; // 366 * 86400

	/** @var int */
	const MONTH_TIMERANGE = 2678400; // 31 * 86400

	/** @var \Runalyze\Dataset\Configuration */
	protected $Configuration;

	/** @var \PDO */
	protected $PDO;

	/** @var int */
	protected $AccountID;

	/** @var bool */
	protected $ShowOnlyPublicActivites = false;

	/** @var array */
	protected $AdditionalColumns = array();

	/** @var array array('table' => array('field' => ..., 'join' => ...)) */
	protected $JoinTables = array();

	/**
	 * @param \Runalyze\Dataset\Configuration $configuration
	 * @param \PDO $pdo
	 * @param int $accountID
	 */
	public function __construct(Configuration $configuration, \PDO $pdo, $accountID)
	{
		$this->Configuration = $configuration;
		$this->PDO = $pdo;
		$this->AccountID = $accountID;
	}

	/**
	 * @param bool $flag
	 */
	public function showOnlyPublicActivities($flag = true)
	{
		$this->ShowOnlyPublicActivites = $flag;
	}

	/**
	 * @param int $timeStart
	 * @param int $timeEnd
	 * @param bool $allKeys
	 * @return \PDOStatement
	 */
	public function statementToFetchActivities($timeStart, $timeEnd, $allKeys = false)
	{
		$this->resetJoins();

		return $this->PDO->query(
			'SELECT
				`t`.`id`,
				`t`.`time`,
				`t`.`s` as `'.Keys\Pace::DURATION_SUM_WITH_DISTANCE_KEY.'`,
				DATE(FROM_UNIXTIME(`t`.`time`)) as `date`,
				'.($allKeys ? $this->queryToSelectAllKeys() : $this->queryToSelectActiveKeys()).'
				'.$this->queryToSelectJoinedFields().'
			FROM `'.PREFIX.'training` AS `t`
			'.$this->queryToJoinTables().'
			WHERE
				'.$this->whereTimeIsBetween($timeStart, $timeEnd).' AND
				`t`.`accountid` = '.(int)$this->AccountID.' AND
				'.$this->wherePrivacyIsOkay().'
			'.$this->queryToGroupByActivity().'
			ORDER BY `time` ASC'
		);
	}

	/**
	 * Reset joins
	 */
	public function resetJoins()
	{
		$this->JoinTables = array();
	}

	/**
	 * @return string
	 */
	public function queryToSelectJoinedFields()
	{
		$query = '';

		foreach ($this->JoinTables as $joinData) {
			$query .= ', '.$joinData['field'];
		}

		return $query;
	}

	/**
	 * @return string
	 */
	public function queryToJoinTables()
	{
		$query = '';

		foreach ($this->JoinTables as $joinData) {
			$query .= $joinData['join'].' ';
		}

		return $query;
	}

	public function queryToGroupByActivity()
	{
		if (!empty($this->JoinTables)) {
			return 'GROUP BY `t`.`id`';
		}

		return '';
	}

	/**
	 * @param int $sportid
	 * @param int $timeStart default 0
	 * @param int $timeEnd   default time()
	 * @return array array with summary for given sportid and timerange
	 */
	public function fetchSummaryForSport($sportid, $timeStart = 0, $timeEnd = false)
	{
		return $this->PDO->query(
			'SELECT
				`time`,
				`sportid`,
				SUM(IF(`distance`>0,`s`,0)) as `'.Keys\Pace::DURATION_SUM_WITH_DISTANCE_KEY.'`,
				SUM(1) as `num`,
				'.$this->queryToSummarizeActiveKeys().'
			FROM `'.PREFIX.'training` AS `t`
			WHERE
				`sportid` = '.(int)$sportid.' AND
				`accountid` = '.(int)$this->AccountID.' AND
				'.$this->whereTimeIsBetween($timeStart, $timeEnd).' AND
				'.$this->wherePrivacyIsOkay().'
			GROUP BY `sportid`
			LIMIT 1'
		)->fetch();
	}

	/**
	 * @param int $timeStart default 0
	 * @param int $timeEnd   default time()
	 * @return array array with summary for each sportid and given timerange
	 */
	public function fetchSummaryForAllSport($timeStart = 0, $timeEnd = false)
	{
		return $this->PDO->query(
			'SELECT
				`time`,
				`sportid`,
				SUM(IF(`distance`>0,`s`,0)) as `'.Keys\Pace::DURATION_SUM_WITH_DISTANCE_KEY.'`,
				SUM(1) as `num`,
				'.$this->queryToSummarizeActiveKeys().'
			FROM `'.PREFIX.'training` AS `t`
			WHERE
				`accountid` = '.(int)$this->AccountID.' AND
				'.$this->whereTimeIsBetween($timeStart, $timeEnd).' AND
				'.$this->wherePrivacyIsOkay().'
			GROUP BY `sportid`'
		)->fetchAll();
	}

	/**
	 * Get summary for a given timerange
	 * @param int $sportid
	 * @param int $timerange default 7*24*60*60
	 * @param int $timeStart default 0
	 * @param int $timeEnd   default time()
	 * @return array array of summaries for different timeranges
	 */
	public function fetchSummaryForTimerange($sportid, $timerange = 604800, $timeStart = 0, $timeEnd = false)
	{
		$Query = '
		        SELECT
				'.(((int)$sportid > 0)?'`sportid`':'0').',
				SUM(IF(`distance`>0,`s`,0)) as `'.Keys\Pace::DURATION_SUM_WITH_DISTANCE_KEY.'`,
				SUM(1) as `num`,
				'.$this->queryToSummarizeActiveKeys().',
				'.$this->queryToSelectTimerange($timerange, $timeEnd, 'timerange').'
			FROM `'.PREFIX.'training` AS `t`
			WHERE
				'.(((int)$sportid > 0)?'`sportid` = '.(int)$sportid.' AND':''). '
				`accountid` = '.(int)$this->AccountID.' AND
				'.$this->whereTimeIsBetween($timeStart, $timeEnd).' AND
				'.$this->wherePrivacyIsOkay().'
				GROUP BY `timerange`
				'.(((int)$sportid > 0)?', `sportid`':'').'
				ORDER BY `timerange` ASC';

		return $this->PDO->query($Query)->fetchAll();
	}

	/**
	 * @return string
	 */
	protected function queryToSummarizeActiveKeys()
	{
		$columnSelects = array();

		foreach ($this->Configuration->activeKeys() as $key) {
			$KeyObject = Keys::get($key);

			if ($KeyObject->isInDatabase() && $KeyObject->isShownInSummary()) {
				$columnSelects[$key] = SummaryMode::query(
					$KeyObject->summaryMode(),
					$KeyObject->column()
				);
			}
		}

		return implode(', ', $columnSelects);
	}

	/**
	 * Query to select all keys
	 * Hint: You must use "... FROM `'.PREFIX.'training` AS `t` ..."
	 * @return string
	 */
	public function queryToSelectAllKeys()
	{
		return $this->queryToSelectKeys(
			$this->collectColumnsForKeys($this->Configuration->allKeys())
		);
	}

	/**
	 * Query to select active keys
	 * Hint: You must use "... FROM `'.PREFIX.'training` AS `t` ..."
	 * @return string
	 */
	public function queryToSelectActiveKeys()
	{
		return $this->queryToSelectKeys(
			$this->collectColumnsForKeys($this->Configuration->activeKeys())
		);
	}

	/**
	 * @param array $arrayOfKeys
	 * @return string
	 */
	protected function queryToSelectKeys(array $arrayOfKeys)
	{
		return '`t`.`'.implode('`, `t`.`', $arrayOfKeys).'`';
	}

	/**
	 * @param array $keys
	 * @return array
	 */
	protected function collectColumnsForKeys(array $keys)
	{
		$columns = $this->defaultColumns();

		foreach ($keys as $key) {
			$KeyObject = Keys::get($key);

			if ($KeyObject->isInDatabase()) {
				if ($KeyObject->requiresJoin()) {
					$joinDefinition = $KeyObject->joinDefinition();

					if (!array_key_exists($joinDefinition['column'], $this->JoinTables)) {
						$this->JoinTables[$joinDefinition['column']] = $joinDefinition;
					}
				} else {
					$appendix = $KeyObject->column();

					if (is_array($appendix)) {
						$columns = array_merge($columns, $appendix);
					} else {
						$columns[] = $appendix;
					}
				}
			}
		}

		return array_unique($columns);
	}

	/**
	 * @return array
	 */
	protected function defaultColumns()
	{
		return array_merge(array('sportid', 'time', 'use_vdot'), $this->AdditionalColumns);
	}

	/**
	 * @param array $columns
	 */
	public function setAdditionalColumns(array $columns)
	{
		$this->AdditionalColumns = $columns;
	}

	/**
	 * @param int $timerange length of time range, typically 366* or 31*DAY_IN_S
	 * @param int $timeEnd last timestamp to consider
	 * @param string $asColumn optional column key that is used in returned data row
	 * @return string
	 */
	protected function queryToSelectTimerange($timerange, $timeEnd = false, $asColumn = 'timerange')
	{
		$timeEnd = $timeEnd ?: time();

		if ($timerange == self::YEAR_TIMERANGE) {
			return date('Y', $timeEnd).' - YEAR(FROM_UNIXTIME(`t`.`time`)) as `'.$asColumn.'`';
		} elseif ($timerange == self::MONTH_TIMERANGE) {
			return date('m', $timeEnd).' - MONTH(FROM_UNIXTIME(`t`.`time`)) + 12*('.date('Y', $timeEnd).' - YEAR(FROM_UNIXTIME(`t`.`time`))) as `'.$asColumn.'`';
		}

		return 'FLOOR(('.$timeEnd.'-`t`.`time`)/('.$timerange.')) as `'.$asColumn.'`';
	}

	/**
	 * @param int $timeStart default: 0
	 * @param int $timeEnd default: time()
	 * @return string
	 */
	protected function whereTimeIsBetween($timeStart = 0, $timeEnd = false)
	{
		$timeEnd = $timeEnd ?: time();

		return '`t`.`time` BETWEEN '.($timeStart - 10).' AND '.($timeEnd - 10);
	}

	/**
	 * Query to respect activity's privacy
	 * @return string
	 */
	protected function wherePrivacyIsOkay() {
		if ($this->ShowOnlyPublicActivites) {
			return '`t`.`is_public`=1';
		}

		return '1';
	}
}