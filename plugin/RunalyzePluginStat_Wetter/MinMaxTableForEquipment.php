<?php
/**
 * This file contains class::MinMaxTableForEquipment
 * @package Runalyze\Plugin\Stat\Wetter
 */

namespace Runalyze\Plugin\Stat\Wetter;

use Runalyze\Activity\Temperature;

/**
 * Table to show max/min/avg temperature for equipment type
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugin\Stat\Wetter
 */
class MinMaxTableForEquipment
{
	/** @var \PDO */
	protected $PDO;

	/** @var int */
	protected $AccountID;

	/** @var int */
	protected $EquipmentTypeID;

	/** @var string */
	protected $DependencyQuery = '';

	/**
	 * @param \PDO $pdo
	 * @param int $accountId
	 * @param int $equipmentTypeId
	 */
	public function __construct(\PDO $pdo, $accountId, $equipmentTypeId)
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
	 * @return \PDOStatement
	 */
	protected function createStatement()
	{
		return $this->PDO->query(
			'SELECT
				AVG(`temperature`) as `avg`,
				MAX(`temperature`) as `max`,
				MIN(`temperature`) as `min`,
				`eq`.`equipmentid`,
				`eqp`.`name`
			FROM `'.PREFIX.'training`
			LEFT JOIN `'.PREFIX.'activity_equipment` AS `eq` ON `id` = `eq`.`activityid`
            LEFT JOIN `'.PREFIX.'equipment` AS `eqp` ON `eq`.`equipmentid` = `eqp`.`id`
			WHERE
				`eqp`.`typeid` = '.(int)$this->EquipmentTypeID.' AND
				`'.PREFIX.'training`.`accountid` = '.(int)$this->AccountID.' AND
				`eq`.`activityid` IS NOT NULL AND
				`temperature` IS NOT NULL
				'.$this->DependencyQuery.'
			GROUP BY `eq`.`equipmentid`'
		);
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
		echo '<table class="fullwidth zebra-style">'.
			'<thead><tr>'.
				'<th></th>'.
				'<th>'.__('Temperatures').'</th>'.
				'<th>&Oslash;</th>'.
				'<th colspan="2"></th>'.
				'<th>'.__('Temperatures').'</th>'.
				'<th>&Oslash;</th>'.
				'<th colspan="2"></th>'.
				'<th>'.__('Temperatures').'</th>'.
				'<th>&Oslash;</th>'.
			'</tr></thead>';
	}

	/**
	 * Display table body
	 */
	protected function displayBody()
	{
		echo '<tbody><tr class="r">';

		$Statement = $this->createStatement();
		$i = 0;

		while ($data = $Statement->fetch()) {
			echo ($i % 3 == 0) ? '<tr class="r">' : '<td>&nbsp;&nbsp;</td>';

			echo '<td class="l">'.$data['name'].'</td>';
			echo '<td>'.(Temperature::format($data['min'], true)).' '.__('to').' '.(Temperature::format($data['max'], true)).'</td>';
			echo '<td>'.(Temperature::format(round($data['avg']), true)).'</td>';

			echo (++$i % 3 == 0) ? '</tr>' : '';
		}

		if ($i % 3 != 0) {
			echo '<td colspan="'.(3 * (3 - $i % 3)).'">&nbsp;</td></tr>';
		} elseif (empty($data)) {
			echo '<td colspan="11" class="c"><em>'.__('No data found.').'</em></td>';
		}

		echo '</tbody>';
	}

	/**
	 * Display table footer
	 */
	protected function displayFoot()
	{
		echo '</table>';
	}
}