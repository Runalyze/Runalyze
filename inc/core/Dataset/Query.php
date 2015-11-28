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
		return $this->PDO->query(
			'SELECT
				`id`,
				`time`,
				`s` as `'.Keys\Pace::DURATION_SUM_WITH_DISTANCE_KEY.'`,
				DATE(FROM_UNIXTIME(time)) as `date`,
				'.($allKeys ? $this->queryToSelectAllKeys() : $this->queryToSelectActiveKeys()).'
			FROM `'.PREFIX.'training`
			WHERE
				'.$this->whereTimeIsBetween($timeStart, $timeEnd).' AND
				`accountid` = '.(int)$this->AccountID.' AND
				'.$this->wherePrivacyIsOkay().'
			ORDER BY `time` ASC'
		);
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
			FROM `'.PREFIX.'training`
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
			FROM `'.PREFIX.'training`
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
		return $this->PDO->query(
			'SELECT
				`sportid`,
				SUM(IF(`distance`>0,`s`,0)) as `'.Keys\Pace::DURATION_SUM_WITH_DISTANCE_KEY.'`,
				SUM(1) as `num`,
				'.$this->queryToSummarizeActiveKeys().',
				'.$this->queryToSelectTimerange($timerange, $timeEnd, 'timerange').'
			FROM `'.PREFIX.'training`
			WHERE
				`sportid` = '.(int)$sportid.' AND
				`accountid` = '.(int)$this->AccountID.' AND
				'.$this->whereTimeIsBetween($timeStart, $timeEnd).' AND
				'.$this->wherePrivacyIsOkay().'
			GROUP BY `timerange`, `sportid`
			ORDER BY `timerange` ASC'
		)->fetchAll();
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
	 * @return string
	 */
	public function queryToSelectAllKeys()
	{
		return $this->queryToSelectKeys(
			$this->collectColumnsForKeys($this->Configuration->allKeys())
		);
	}

	/**
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
		return '`'.implode('`, `', $arrayOfKeys).'`';
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
				$columns[] = $KeyObject->column();
			}
		}

		return array_unique($columns);
	}

	/**
	 * @return array
	 */
	protected function defaultColumns()
	{
		return array_merge(array('sportid', 'time'), $this->AdditionalColumns);
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
			return date('Y', $timeEnd).' - YEAR(FROM_UNIXTIME(`time`)) as `'.$asColumn.'`';
		} elseif ($timerange == self::MONTH_TIMERANGE) {
			return date('m', $timeEnd).' - MONTH(FROM_UNIXTIME(`time`)) + 12*('.date('Y', $timeEnd).' - YEAR(FROM_UNIXTIME(`time`))) as `'.$asColumn.'`';
		}

		return 'FLOOR(('.$timeEnd.'-`time`)/('.$timerange.')) as `'.$asColumn.'`';
	}

	/**
	 * @param int $timeStart default: 0
	 * @param int $timeEnd default: time()
	 * @return string
	 */
	protected function whereTimeIsBetween($timeStart = 0, $timeEnd = false)
	{
		$timeEnd = $timeEnd ?: time();

		return '`time` BETWEEN '.($timeStart - 10).' AND '.($timeEnd - 10);
	}

	/**
	 * Query to respect activity's privacy
	 * @return string
	 */
	protected function wherePrivacyIsOkay() {
		if ($this->ShowOnlyPublicActivites) {
			return '`is_public`=1';
		}

		return '1';
	}
}