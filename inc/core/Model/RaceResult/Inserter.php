<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\RaceResult
 */

namespace Runalyze\Model\RaceResult;

use Runalyze\Model;
use Runalyze\Configuration;
/**
 * Insert RaceResult to database
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\RaceResult
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * Object
	 * @var \Runalyze\Model\RaceResult\Entity
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\RaceResult\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'raceresult';
	}

	/**
	 * Keys to insert
	 * @return array
	 */
	protected function keys() {
		return array_merge(array(
				self::ACCOUNTID
			),
			Entity::allDatabaseProperties()
		);
	}
	
	/**
	 * Tasks after insertion
	 */
	protected function after() {
		$this->updateVDOTcorrector();
	}
	
	/**
	 * Update vdot corrector
	 */
	protected function updateVDOTcorrector() {
		$Factory = new Model\Factory();
		$Activity = $Factory->activity($this->Object->activityId());
		if (
			$Activity->sportid() == Configuration::General()->runningSport() &&
			$Activity->usesVDOT() &&
			$Activity->vdotByHeartRate() > 0
		) {
				Configuration::Data()->recalculateVDOTcorrector();
		}
	}
}