<?php
/**
 * This file contains class::Deleter
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

use Runalyze\Model;
use Runalyze\Calculation\BasicEndurance;
use Runalyze\Configuration;

/**
 * Deleter activity in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class Deleter extends Model\DeleterWithIDAndAccountID {
	/**
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $Object;

	/**
	 * @var array
	 */
	protected $EquipmentIDs = array();
	
	/**
	 * @var array
	 */
	protected $TagIDs = array();

	/**
	 * Construct deleter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Activity\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * @param array $ids
	 */
	public function setEquipmentIDs(array $ids) {
		$this->EquipmentIDs = $ids;
	}
	
	/**
	 * @param array $ids
	 */
	public function setTagIDs(array $ids) {
		$this->TagIDs = $ids;
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'training';
	}

	/**
	 * Tasks after insertion
	 */
	protected function after() {
		$this->deleteTrackdata();
		$this->deleteSwimdata();
		$this->deleteRoute();

		$this->updateEquipment();
		$this->updateTag();
		$this->updateStartTime();
		$this->updateVDOTshapeAndCorrector();
		$this->updateBasicEndurance();
	}

	/**
	 * Delete trackdata
	 */
	protected function deleteTrackdata() {
		$Deleter = new Model\Trackdata\Deleter($this->PDO, new Model\Trackdata\Entity(array(
			'activityid' => $this->Object->id()
		)));
		$Deleter->setAccountID($this->AccountID);
		$Deleter->delete();
	}

	/**
	 * Delete trackdata
	 */
	protected function deleteSwimdata() {
		$Deleter = new Model\Swimdata\Deleter($this->PDO, new Model\Swimdata\Entity(array(
			'activityid' => $this->Object->id()
		)));
		$Deleter->setAccountID($this->AccountID);
		$Deleter->delete();
	}

	/**
	 * Delete route
	 */
	protected function deleteRoute() {
		if ($this->Object->get(Model\Activity\Entity::ROUTEID) > 0) {
			// TODO: check if route was uniquely used
			// For the moment, routes are created uniquely, so that's right.
			$Deleter = new Model\Route\Deleter($this->PDO, new Model\Route\Entity(array(
				'id' => $this->Object->get(Model\Activity\Entity::ROUTEID)
			)));
			$Deleter->setAccountID($this->AccountID);
			$Deleter->delete();
		}
	}

	/**
	 * Update equipment
	 */
	protected function updateEquipment() {
		if (!empty($this->EquipmentIDs)) {
	        $EquipmentUpdater = new EquipmentUpdater($this->PDO, $this->Object->id());
			$EquipmentUpdater->setActivityObjects(new Entity(), $this->Object);
			$EquipmentUpdater->update(array(), $this->EquipmentIDs);
		}
	}
	
	/**
	 * Update tag
	 */
	protected function updateTag() {
		if (!empty($this->TagIDs)) {
	        $TagUpdater = new TagUpdater($this->PDO, $this->Object->id());
			$TagUpdater->update(array(), $this->TagIDs);
		}
	}
	
	/**
	 * Update start time
	 */
	protected function updateStartTime() {
		if ($this->Object->timestamp() <= Configuration::Data()->startTime()) {
			Configuration::Data()->recalculateStartTime();
		}
	}

	/**
	 * Update vdot shape and corrector
	 */
	protected function updateVDOTshapeAndCorrector() {
		$timestampLimit = time() - Configuration::Vdot()->days() * DAY_IN_S;

		if (
			$this->Object->vdotByHeartRate() > 0 &&
			$this->Object->usesVDOT() &&
			$this->Object->timestamp() > $timestampLimit
		) {
			Configuration::Data()->recalculateVDOTshape();

			if ($this->Object->typeid() == Configuration::General()->competitionType()) {
				Configuration::Data()->recalculateVDOTcorrector();
			}
		}
	}

	/**
	 * Update basic endurance
	 */
	protected function updateBasicEndurance() {
		$timestampLimit = time() - 182 * DAY_IN_S;

		if (
			$this->Object->timestamp() > $timestampLimit &&
			$this->Object->sportid() == Configuration::General()->runningSport()
		) {
			BasicEndurance::recalculateValue();
		}
	}
}