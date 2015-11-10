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
	/** @var \Runalyze\Dataset\Configuration */
	protected $Configuration;

	/** @var \PDO */
	protected $PDO;

	/** @var int */
	protected $AccountID;

	/** @var bool */
	protected $ShowOnlyPublicActivites = false;

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
	 * @param int $sportid
	 * @param int $timeStart default 0
	 * @param int $timeEnd   default time()
	 * @return array array with summary for given sportid and timerange
	 */
	public function fetchSummary($sportid, $timeStart = 0, $timeEnd = 0)
	{
		return $this->PDO->query('
			SELECT
				`time`,
				`sportid`,
				SUM(IF(`distance`>0,`s`,0)) as `'.Keys\Pace::DURATION_SUM_WITH_DISTANCE_KEY.'`,
				SUM(1) as `num`,
				'.$this->queryToSummarizeActiveKeys().'
			FROM `'.PREFIX.'training`
			WHERE
				`sportid` = '.(int)$sportid.' AND
				`accountid` = '.(int)$this->AccountID.' AND
				`time` BETWEEN '.($timeStart - 10).' AND '.($timeEnd - 10).'
				'.$this->queryToRespectPrivacy().'
			GROUP BY `sportid`
			LIMIT 1
		')->fetch();
	}

	/**
	 * Get summary for a given timerange
	 * @param int $sportid
	 * @param int $timerange default 7*24*60*60
	 * @param int $timeStart default 0
	 * @param int $timeEnd   default time()
	 * @return array array of summaries for different timeranges
	 */
	public function fetchSummaryForTimerange($sportid, $timerange = 604800, $timeStart = 0, $timeEnd = 0)
	{
		if ($timeEnd == 0) {
			$timeEnd = time();
		}

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
				`time` BETWEEN '.($timeStart - 10).' AND '.($timeEnd - 10).'
				'.$this->queryToRespectPrivacy().'
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
		return array('sportid', 'time');
	}

	/**
	 * @param int $timerange length of time range, typically 366* or 31*DAY_IN_S
	 * @param int $timeEnd last timestamp to consider
	 * @param string $asColumn optional column key that is used in returned data row
	 * @return string
	 */
	protected function queryToSelectTimerange($timerange, $timeEnd, $asColumn = 'timerange')
	{
		if ($timerange == 366*DAY_IN_S) {
			return date('Y', $timeEnd).' - YEAR(FROM_UNIXTIME(`time`)) as `'.$asColumn.'`';
		} elseif ($timerange == 31*DAY_IN_S) {
			return date('m', $timeEnd).' - MONTH(FROM_UNIXTIME(`time`)) + 12*('.date('Y', $timeEnd).' - YEAR(FROM_UNIXTIME(`time`))) as `'.$asColumn.'`';
		}

		return 'FLOOR(('.$timeEnd.'-`time`)/('.$timerange.')) as `'.$asColumn.'`';
	}

	/**
	 * Query to respect activity's privacy
	 * @return string
	 */
	protected function queryToRespectPrivacy() {
		if ($this->ShowOnlyPublicActivites) {
			return ' AND `is_public`=1 ';
		}

		return '';
	}
}