<?php
/**
 * This file contains class::MonthwiseTable
 * @package Runalyze\Plugin\Stat\Wetter
 */

namespace Runalyze\Plugin\Stat\Wetter;

use Runalyze\Activity\Temperature;
use Runalyze\Data\Weather;
use Runalyze\Model\Factory;
use Runalyze\Util\Time;

/**
 * Table to show monthwise statistics
 *
 * @author Hannes Christiansen
 * @package Runalyze\Plugin\Stat\Wetter
 */
class MonthwiseTable
{
	/** @var \PDO */
	protected $PDO;

	/** @var int */
	protected $AccountID;

	/** @var int */
	protected $EquipmentTypeID;

	/** @var string */
	protected $DependencyQuery = '';

	/** @var string */
	protected $GroupByQuery = '';

	/** @var string */
	protected $OrderByQuery = '';

	/** @var int */
	protected $MonthOffsetForHeader = -1;

	/**
	 * @param \PDO $pdo
	 * @param int $accountId
	 * @param int $equipmentTypeId
	 */
	public function __construct(\PDO $pdo, $accountId, $equipmentTypeId = 0)
	{
		$this->PDO = $pdo;
		$this->AccountID = $accountId;
		$this->EquipmentTypeID = $equipmentTypeId;
	}

	/**
	 * @param string $query
	 */
	public function setDependency($query)
	{
		$this->DependencyQuery = $query;
	}

	/**
	 * @param string $query
	 */
	public function setGroupBy($query)
	{
		$this->GroupByQuery = $query;
	}

	/**
	 * @param string $query
	 */
	public function setOrderBy($query)
	{
		$this->OrderByQuery = $query;
	}

	/**
	 * @param int $offset
	 */
	public function setMonthOffset($offset)
	{
		$this->MonthOffsetForHeader = $offset;
	}

	/**
	 * @throws \RuntimeException
	 */
	protected function checkSettings()
	{
		if (empty($this->GroupByQuery) || empty($this->OrderByQuery)) {
			throw new \RuntimeException('Group by and order by queries must be set.');
		}
	}

	/**
	 * Display table
	 */
	public function display()
	{
		$this->displayHead();
		$this->displayBody();
		$this->displayFoot();
	}

	/**
	 * Display table head
	 */
	protected function displayHead()
	{
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>';
		echo '<th></th>';

		for ($i = 1; $i <= 12; $i++) {
			echo '<th width="7.5%">'.Time::month(($i + $this->MonthOffsetForHeader)%12 + 1, true).'</th>';
		}

		echo '</thead>';
		echo '<tbody>';
	}

	/**
	 * Display table body
	 */
	protected function displayBody()
	{
		$this->displayBodyForData($this->generateDataForAverageTemperature());
		$this->displayBodyForData($this->generateDataForWeatherConditions());
		$this->displayBodyForData($this->generateDataForEquipment());
	}

	/**
	 * @param array $rows
	 */
	protected function displayBodyForData(array $rows)
	{
		$isFirst = true;
		$mentionEmptyData = (count($rows) == 1);

		foreach ($rows as $name => $rowData) {
			echo '<tr'.($isFirst ? ' class="top-spacer"' : '').'>';
			echo '<td>'.$name.'</td>';

			if (empty($rowData)) {
				echo '<td colspan="12" class="c">'.($mentionEmptyData ? '<em>'.__('No data found.').'</em>' : '').'</td>';
			} else {
				for ($i = 1; $i <= 12; ++$i) {
					echo '<td>'.(isset($rowData[$i]) ? $rowData[$i] : '').'</td>';
				}
			}

			echo '</tr>';

			$isFirst = false;
		}
	}

	/**
	 * Display table footer
	 */
	protected function displayFoot()
	{
		echo '</table>';
	}

	/**
	 * @return array
	 */
	protected function generateDataForAverageTemperature()
	{
		$Temperature = new Temperature;
		$Statement = $this->createStatementToFetchAverageTemperatures();
		$data = array();

		while ($row = $Statement->fetch()) {
			$data[$row['m']] = $Temperature->format(round($row['temp']));
		}

		return array($Temperature->unit() => $data);
	}

	/**
	 * @return \PDOStatement
	 */
	protected function createStatementToFetchAverageTemperatures()
	{
		return $this->PDO->query(
			'SELECT
				AVG(`temperature`) as `temp`,
				'.$this->GroupByQuery.' as `m`
			FROM `'.PREFIX.'training`
			WHERE
				`accountid`='.\SessionAccountHandler::getId().' AND
				`temperature` IS NOT NULL
				'.$this->DependencyQuery.'
			GROUP BY '.$this->GroupByQuery.'
			ORDER BY '.$this->OrderByQuery.' ASC
			LIMIT 12'
		);
	}

	/**
	 * @return array
	 */
	protected function generateDataForWeatherConditions()
	{
		$Rows = array();
		$Condition = new Weather\Condition(0);
		$Statement = $this->createStatementToFetchWeatherConditions();

		foreach (Weather\Condition::completeList() as $id) {
			if ($id == Weather\Condition::UNKNOWN) {
				continue;
			}

			$Statement->execute(array($id));
			$Condition->set($id);
			$rowData = array();

			foreach ($Statement->fetchAll() as $data) {
				$rowData[$data['m']] = $data['num'].'x';
			}

			$Rows[$Condition->icon()->code()] = $rowData;
		}

		return $Rows;
	}

	/**
	 * @return \PDOStatement
	 */
	protected function createStatementToFetchWeatherConditions()
	{
		return $this->PDO->prepare(
			'SELECT
				SUM(1) as `num`,
				'.$this->GroupByQuery.' as `m`
			FROM `'.PREFIX.'training`
			WHERE
				`accountid`='.\SessionAccountHandler::getId().' AND
				`weatherid` = ?
				'.$this->DependencyQuery.'
			GROUP BY '.$this->GroupByQuery.'
			ORDER BY '.$this->OrderByQuery.' ASC
			LIMIT 12'
		);
	}

	/**
	 * @return array
	 */
	protected function generateDataForEquipment()
	{
		if ($this->EquipmentTypeID == 0) {
			return array();
		}

		$RowsByID = array();
		$Rows = array();
		$Factory = new Factory($this->AccountID);
		$Statement = $this->createStatementToFetchEquipment();

		while ($data = $Statement->fetch()) {
			if (!isset($RowsByID[$data['equipmentid']])) {
				$RowsByID[$data['equipmentid']] = array();
			}

			$RowsByID[$data['equipmentid']][$data['m']] = $data['num'].'x';
		}

		foreach ($RowsByID as $id => $rowData) {
			$Rows[$Factory->equipment($id)->name()] = $rowData;
		}

		return $Rows;
	}

	/**
	 * @return \PDOStatement
	 */
	protected function createStatementToFetchEquipment()
	{
		return $this->PDO->query(
			'SELECT
				SUM(IF(`eq`.`activityid` = `'.PREFIX.'training`.`id`, 1,0)) as `num`,
				`eq`.`equipmentid`,
				'.$this->GroupByQuery.' as `m`
			FROM `'.PREFIX.'activity_equipment` AS `eq`
			LEFT JOIN `'.PREFIX.'training` ON `'.PREFIX.'training`.`id` = `eq`.`activityid`
            LEFT JOIN `'.PREFIX.'equipment` AS `eqp` ON `eq`.`equipmentid` = `eqp`.`id`
			WHERE
				`'.PREFIX.'training`.`accountid` = '.$this->AccountID.' AND
				`eqp`.`typeid` = '.$this->EquipmentTypeID.'
				'.$this->DependencyQuery.'
			GROUP BY `eq`.`equipmentid`, '.$this->GroupByQuery.' HAVING `num` != 0
			ORDER BY `eq`.`equipmentid` ASC, '.$this->OrderByQuery.' ASC'
		);
	}
}
