<?php
/**
 * This file contains class::StatisticsUpdater
 * @package Runalyze\Model\Equipment
 */

namespace Runalyze\Model\Equipment;

/**
 * Update collected statistics (distance, time) for equipment
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Equipment
 */
class StatisticsUpdater {
	/**
	 * Database connection
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * Account id
	 * @var int|null
	 */
	protected $accountID;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param int $accountID [optional]
	 */
	public function __construct(\PDO $connection, $accountID = null) {
		$this->PDO = $connection;
		$this->accountID = $accountID;
	}

	/**
	 * Update statistics
	 * @return mixed false on failure, number of affected rows otherwise
	 */
	public function run() {
		$whereAccount = null !== $this->accountID ? 'WHERE `eqp`.`accountid` = '.$this->accountID : '';
		$result = $this->PDO->exec(
			'UPDATE `'.PREFIX.'equipment`
			CROSS JOIN(
				SELECT
					`eqp`.`id` AS `eqpid`,
					SUM(`tr`.`distance`) AS `km`,
					SUM(`tr`.`s`) AS `s` 
				FROM `'.PREFIX.'equipment` AS `eqp` 
				LEFT JOIN `'.PREFIX.'activity_equipment` AS `aeqp` ON `eqp`.`id` = `aeqp`.`equipmentid` 
				LEFT JOIN `'.PREFIX.'training` AS `tr` ON `aeqp`.`activityid` = `tr`.`id`
				'.$whereAccount.'
				GROUP BY `eqp`.`id`
			) AS `new`
			SET
				`distance` = IFNULL(`new`.`km`, 0),
				`time` = IFNULL(`new`.`s`, 0)
			WHERE `id` = `new`.`eqpid`');

		if ($result !== false) {
			$Factory = new \Runalyze\Model\Factory($this->accountID);
			$Factory->clearCache('equipment');
		}

		return $result;
	}
}