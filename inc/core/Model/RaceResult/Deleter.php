<?php
/**
 * This file contains class::Deleter
 * @package Runalyze\Model\RaceResult
 */

namespace Runalyze\Model\RaceResult;

use Cache;
use Runalyze\Model\DeleterWithAccountID;
use Runalyze\Model;
use Runalyze\Configuration;

/**
 * Delete object in database
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Model\RaceResult
 */
class Deleter extends DeleterWithAccountID
{
	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\RaceResult\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null)
	{
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table()
	{
		return 'raceresult';
	}
	
	/**
	 * Where clause
	 * @return string
	 */
	protected function where()
	{
		return '`activity_id`='.$this->Object->get(Entity::ACTIVITY_ID).' AND '.parent::where();
	}
	
	/**
	 * Tasks before delete
	 * @throws \RuntimeException
	 */
	protected function before()
	{
		parent::before();

		if (!$this->Object->get(Entity::ACTIVITY_ID)) {
			throw new \RuntimeException('Provided object does not have any activityid.');
		}

		(new Model\Factory($this->AccountID))->clearCache($this->table(), $this->Object->get(Entity::ACTIVITY_ID));
	}
	
	/**
	 * Tasks after delete
	 */
	protected function after()
	{
		$this->updateVDOTcorrector();
	}
	
	/**
	 * Update vdot corrector
	 */
	protected function updateVDOTcorrector()
	{
		$Activity = (new Model\Factory($this->AccountID))->activity($this->Object->activityId());

		if (
			$Activity->sportid() == Configuration::General()->runningSport() &&
			$Activity->usesVDOT() &&
			$Activity->vdotByHeartRate() > 0
		) {
			Configuration::Data()->recalculateVDOTcorrector();
		}
	}
}
