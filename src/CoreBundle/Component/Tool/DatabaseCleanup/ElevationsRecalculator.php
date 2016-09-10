<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup;

use Runalyze\Calculation;

class ElevationsRecalculator
{
	/** @var int */
	protected $NumberOfRoutes = 0;

	/** @var array calculated elevations */
	protected $Results = array();

	/** @var \PDO */
	protected $PDO;

	/** @var int */
	protected $AccountID;

    /** @var string */
    protected $DatabasePrefix;

	/**
	 * Construct
	 * @param \PDO $database
	 * @param int $accountId
     * @param string $databasePrefix
	 */
	public function __construct(\PDO $database, $accountId, $databasePrefix)
    {
		$this->PDO = $database;
		$this->AccountID = $accountId;
        $this->DatabasePrefix = $databasePrefix;
	}

	/**
	 * Run recalculations
	 */
	public function run()
    {
		$Query = $this->getQuery();
		$Update = $this->prepareUpdate();

		while ($Data = $Query->fetch()) {
			$Elevation = \Runalyze\Model\Entity::explode($Data['elevations']);
			$Calculator = new Calculation\Elevation\Calculator($Elevation);
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
	protected function prepareUpdate()
    {
		$Set = array(
			'`elevation` = :elevation',
			'`elevation_up` = :elevation_up',
			'`elevation_down` = :elevation_down'
		);

		$Query = 'UPDATE `'.$this->DatabasePrefix.'route` SET '.implode(',', $Set).' WHERE `id`=:id AND `accountid`="'.(int)$this->AccountID.'"';

		return $this->PDO->prepare($Query);
	}

	/**
	 * Get query statement
	 * @return \PDOStatement statement to fetch routes with respective elevations
	 */
	protected function getQuery()
    {
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
			FROM `'.$this->DatabasePrefix.'route`
			WHERE
				`accountid`="'.(int)$this->AccountID.'" AND
				(`elevations_original`!="" OR `elevations_corrected`!="")'
		);
	}

	/**
	 * @return int number of routes that have been touched
	 */
	public function numberOfRoutes()
    {
		return $this->NumberOfRoutes;
	}

	/**
	 * @return array 'activityid' => array('total', 'up', 'down')
	 */
	public function results()
    {
		return $this->Results;
	}
}
