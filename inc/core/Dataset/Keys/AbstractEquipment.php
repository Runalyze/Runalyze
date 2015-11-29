<?php
/**
 * This file contains class::AbstractEquipment
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

/**
 * Abstract class for dataset keys that require equipment
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
abstract class AbstractEquipment extends AbstractKey
{
	/** @var string */
	const CONCAT_EQUIPMENT_KEY = 'concat_equipment';

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return '';
	}

	/**
	 * @return bool
	 */
	public function requiresJoin()
	{
		return true;
	}

	/**
	 * @return array array('column' => '...', 'join' => 'LEFT JOIN ...', 'field' => '`x`.`y`)
	 */
	public function joinDefinition()
	{
		return array(
			'column' => self::CONCAT_EQUIPMENT_KEY,
			'join' => 'LEFT JOIN `'.PREFIX.'activity_equipment` AS `aeqp` ON `t`.`id` = `aeqp`.`activityid`',
			'field' => 'GROUP_CONCAT(`aeqp`.`equipmentid` SEPARATOR \',\') AS `'.self::CONCAT_EQUIPMENT_KEY.'`'
		);
	}
}