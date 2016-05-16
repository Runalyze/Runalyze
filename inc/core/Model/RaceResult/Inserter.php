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
	
	/** @var \Runalyze\Model\Activity\Entity */
	protected $ActivityObject;

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
	 * @throws \RuntimeException
	 */
	protected function before() {
		$this->loadActivityObject();

		parent::before();
	}

	/**
	 * @throws \RuntimeException
	 */
	protected function loadActivityObject() {
		$this->ActivityObject = (new Model\Factory($this->AccountID))->activity($this->Object->activityId());

		if (!$this->ActivityObject->hasID()) {
			throw new \RuntimeException('There is no valid activity object for this race result entity.');
		}
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
		if (
			$this->ActivityObject->sportid() == Configuration::General()->runningSport() &&
			$this->ActivityObject->usesVDOT() &&
			$this->ActivityObject->vdotByHeartRate() > 0
		) {
			Configuration::Data()->recalculateVDOTcorrector();
		}
	}
}