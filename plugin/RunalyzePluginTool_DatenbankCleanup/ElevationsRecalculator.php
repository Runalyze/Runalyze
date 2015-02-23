<?php
/**
 * This file contains class::ElevationsRecalculator
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */

namespace Runalyze\Plugin\Tool\DatabaseCleanup;

use Runalyze\Data\Elevation;

use SessionAccountHandler;

/**
 * Recalculate elevations
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */
class ElevationsRecalculator {
	/**
	 * Number of routes
	 * @var int
	 */
	protected $NumberOfRoutes = 0;

	/**
	 * Calculated elevations
	 * @var array
	 */
	protected $Results = array();

	/**
	 * PDO
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * Account ID
	 * @var int
	 */
	protected $AccountID;

	/**
	 * Construct
	 * @param \PDO $database
	 * @param int $accountID [optional]
	 */
	public function __construct(\PDO $database, $accountID = 'auto') {
		$this->PDO = $database;

		if ($accountID === 'auto') {
			$this->AccountID = SessionAccountHandler::getId();
		} else {
			$this->AccountID = $accountID;
		}
	}

	/**
	 * Run recalculations
	 */
	public function run() {
		$Query = $this->getQuery();
		$Update = $this->prepareUpdate();

		while ($Data = $Query->fetch()) {
			$Elevation = \Runalyze\Model\Object::explode($Data['elevations']);
			$Calculator = new Elevation\Calculation\Calculator($Elevation);
			$Calculator->calculate();

			if ($Calculator->totalElevation() != $Data['elevation'] || $Calculator->elevationUp() != $Data['elevation_up']) {
				$Update->bindValue(':id', $Data['id']);
				$Update->bindValue(':elevation', $Calculator->totalElevation());
				$Update->bindValue(':elevation_up', $Calculator->elevationUp());
				$Update->bindValue(':elevation_down', $Calculator->elevationDown());
				$Update->execute();

				$this->NumberOfRoutes++;
			}

			$this->Results[$Data['id']] = array($Calculator->totalElevation(), $Calculator->elevationUp(), $Calculator->elevationDown());
		}
	}

	/**
	 * Prepare statement
	 * @return \PDOStatement
	 */
	protected function prepareUpdate() {
		$Set = array(
			'`elevation` = :elevation',
			'`elevation_up` = :elevation_up',
			'`elevation_down` = :elevation_down'
		);

		$Query = 'UPDATE `'.PREFIX.'route` SET '.implode(',', $Set).' WHERE `id`=:id AND `accountid`="'.(int)$this->AccountID.'"';

		return $this->PDO->prepare($Query);
	}

	/**
	 * Get query statement
	 * @return \PDOStatement
	 */
	protected function getQuery() {
		return $this->PDO->query(
			'SELECT
				`id`,
				`elevation`,
				`elevation_up`,
				(CASE
					WHEN  `elevations_corrected` !=  ""
					THEN  `elevations_corrected` 
					ELSE  `elevations_original` 
				END) AS  `elevations` 
			FROM `'.PREFIX.'route`
			WHERE
				`accountid`="'.(int)$this->AccountID.'" AND
				(`elevations_original`!="" OR `elevations_corrected`!="")'
		);
	}

	/**
	 * Number of touched routes
	 * @return int
	 */
	public function numberOfRoutes() {
		return $this->NumberOfRoutes;
	}

	/**
	 * Results
	 * @return array 'activityid' => array('total', 'up', 'down')
	 */
	public function results() {
		return $this->Results;
	}
}