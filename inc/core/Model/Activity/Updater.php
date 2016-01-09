<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

use Runalyze\Model;
use Runalyze\Calculation\Activity\VerticalRatioCalculator;
use Runalyze\Calculation\BasicEndurance;
use Runalyze\Configuration;



/**
 * Update activity in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class Updater extends Model\UpdaterWithIDAndAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $NewObject;

	/**
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Trackdata = null;

	/**
	 * @var \Runalyze\Model\Route\Entity
	 */
	protected $Route = null;

	/**
	 * @var array
	 */
	protected $EquipmentIDsNew = array();

	/**
	 * @var array
	 */
	protected $EquipmentIDsOld = array();
	
	/**
	 * @var array
	 */
	protected $TagIDsNew = array();

	/**
	 * @var array
	 */
	protected $TagIDsOld = array();

	/**
	 * @var boolean
	 */
	protected $ForceRecalculations = false;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Activity\Entity $newObject [optional]
	 * @param \Runalyze\Model\Activity\Entity $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Entity $newObject = null, Entity $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 */
	public function setTrackdata(Model\Trackdata\Entity $trackdata) {
		$this->Trackdata = $trackdata;
	}

	/**
	 * @param \Runalyze\Model\Route\Entity $route
	 */
	public function setRoute(Model\Route\Entity $route) {
		$this->Route = $route;
	}

	/**
	 * @param array $newIDs
	 * @param array $oldIDs
	 */
	public function setEquipmentIDs(array $newIDs, array $oldIDs) {
		$this->EquipmentIDsNew = $newIDs;
		$this->EquipmentIDsOld = $oldIDs;
	}
	
	/**
	 * @param array $newTagIDs
	 * @param array $oldTagIDs
	 */
	public function setTagIDs(array $newTagIDs, array $oldTagIDs) {
		$this->TagIDsNew = $newTagIDs;
		$this->TagIDsOld = $oldTagIDs;
	}

	/**
	 * @param boolean $flag
	 */
	public function forceRecalculations($flag = true) {
		$this->ForceRecalculations = $flag;
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'training';
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
	 * Ignore specific key
	 * @param string $key
	 * @return boolean
	 */
	protected function ignore($key) {
		if ($key == Entity::DISTANCE || $key == Entity::TIME_IN_SECONDS) {
			// TODO: needed if equipment is set
			if ($this->OldObject == null && false) {
				throw new \RuntimeException('For an update of distance or duration the old object has to be set.');
			}
		}

		return parent::ignore($key);
	}

	/**
	 * Tasks before insertion
	 */
	protected function before() {
		parent::before();

		$this->NewObject->set(Entity::TIMESTAMP_EDITED, time());

		$this->updateVDOTAndIntensityAndTrimp();
		$this->deleteIntensityCache();
		$this->updatePower();
		$this->updateStrideLength();
		$this->updateVerticalRatio();
	}

	/**
	 * Update VDOT, jd intensity, TRIMP
	 */
	protected function updateVDOTAndIntensityAndTrimp() {
		$Calculator = new \Runalyze\Calculation\Activity\Calculator(
			$this->NewObject,
			$this->Trackdata,
			$this->Route
		);

		if ($this->NewObject->sportid() == Configuration::General()->runningSport()) {
			$wasNotRunning = $this->knowsOldObject() && $this->hasChanged(Entity::SPORTID);
			if ($this->ForceRecalculations || $wasNotRunning || $this->hasChanged(Entity::TIME_IN_SECONDS) || $this->hasChanged(Entity::DISTANCE) || $this->hasChanged(Entity::HR_AVG) || $this->hasChanged(Entity::ELEVATION)) {
				$this->NewObject->set(Entity::VDOT_BY_TIME, $Calculator->calculateVDOTbyTime());
				$this->NewObject->set(Entity::JD_INTENSITY, $Calculator->calculateJDintensity());
				$this->NewObject->set(Entity::VDOT, $Calculator->calculateVDOTbyHeartRate());
				$this->NewObject->set(Entity::VDOT_WITH_ELEVATION, $Calculator->calculateVDOTbyHeartRateWithElevation());
			}
		} else {
			$this->NewObject->unsetRunningValues();
		}

		if ($this->ForceRecalculations || (
				(null === $this->Trackdata || !$this->Trackdata->has(Model\Trackdata\Entity::TIME) || !$this->Trackdata->has(Model\Trackdata\Entity::HEARTRATE)) && (
					$this->hasChanged(Entity::SPORTID) || $this->hasChanged(Entity::TIME_IN_SECONDS) || $this->hasChanged(Entity::HR_AVG)
				)
			)
		) {
			$this->NewObject->set(Entity::TRIMP, $Calculator->calculateTrimp());
		}
	}

	protected function deleteIntensityCache() {
		if (!class_exists('RunalyzePluginPanel_Rechenspiele')) {
			return;
		}

		$timestampLimit = time() - 14 * DAY_IN_S;

		if (
			($this->hasChanged(Entity::JD_INTENSITY) && (
				$this->NewObject->timestamp() >= $timestampLimit ||
				($this->knowsOldObject() && $this->OldObject->timestamp() >= $timestampLimit)
			)) || (
				$this->knowsOldObject() && $this->hasChanged(Entity::TIMESTAMP) && (
					$this->NewObject->timestamp() >= $timestampLimit ||
					$this->OldObject->timestamp() >= $timestampLimit
				)
			)
		) {
			\Cache::delete(\RunalyzePluginPanel_Rechenspiele::CACHE_KEY_JD_POINTS);
		}
	}

	/**
	 * Update power
	 */
	protected function updatePower() {
		if ($this->hasChanged(Entity::SPORTID)) {
			if (
				\Runalyze\Context::Factory()->sport($this->NewObject->sportid())->hasPower() &&
				null !== $this->Trackdata &&
				$this->Trackdata->has(Model\Trackdata\Entity::TIME) && 
				$this->Trackdata->has(Model\Trackdata\Entity::DISTANCE)
			) {
				$Calculator = new \Runalyze\Calculation\Power\Calculator(
					$this->Trackdata,
					$this->Route
				);
				$Calculator->calculate();

				$this->updatePowerForTrackdata($Calculator->powerData());
				$this->NewObject->set(Entity::POWER, $Calculator->average());
			} else {
				$this->updatePowerForTrackdata(array());
				$this->NewObject->set(Entity::POWER, 0);
			}
		}
	}

	/**
	 * Update power for trackdata
	 * @param array $powerData
	 */
	protected function updatePowerForTrackdata(array $powerData) {
		if (
			(null !== $this->Trackdata) && (
				(empty($powerData) && $this->Trackdata->has(Model\Trackdata\Entity::POWER)) ||
				(!empty($powerData) && !$this->Trackdata->has(Model\Trackdata\Entity::POWER))
			)
		) {
			$this->Trackdata->set(Model\Trackdata\Entity::POWER, $powerData);

			$TrackdataUpdater = new Model\Trackdata\Updater($this->PDO);
			$TrackdataUpdater->setAccountID($this->AccountID);
			$TrackdataUpdater->update($this->Trackdata, array(Model\Trackdata\Entity::POWER));
		}
	}

	/**
	 * Update stride length
	 */
	protected function updateStrideLength() {
		if (
			$this->hasChanged(Entity::SPORTID) ||
			$this->hasChanged(Entity::VERTICAL_OSCILLATION) ||
			$this->hasChanged(Entity::STRIDE_LENGTH)
		) {
			if ($this->NewObject->sportid() == Configuration::General()->runningSport()) {
				$this->NewObject->set(Entity::STRIDE_LENGTH, \Runalyze\Calculation\StrideLength\Calculator::forActivity($this->NewObject));
			} else {
				$this->NewObject->set(Entity::STRIDE_LENGTH, 0);
			}
		}
	}

	/**
	 * Update vertical ratio
	 */
	protected function updateVerticalRatio() {
		if (
			$this->hasChanged(Entity::SPORTID) ||
			$this->hasChanged(Entity::VERTICAL_OSCILLATION) ||
			$this->hasChanged(Entity::STRIDE_LENGTH)
		) {
			$this->NewObject->set(Entity::VERTICAL_RATIO, VerticalRatioCalculator::forActivity($this->NewObject));
		}
	}
        
	/**
	 * Tasks after insertion
	 */
	protected function after() {
		parent::after();

		$this->updateEquipment();
		$this->updateTag();
		$this->updateStartTime();
		$this->updateVDOTshapeAndCorrector();
		$this->updateBasicEndurance();
	}

	/**
	 * Update equipment
	 */
	protected function updateEquipment() {
		if (!empty($this->EquipmentIDsNew) || !empty($this->EquipmentIDsOld)) {
	        $EquipmentUpdater = new EquipmentUpdater($this->PDO, $this->NewObject->id());
			$EquipmentUpdater->setActivityObjects($this->NewObject, $this->OldObject);
			$EquipmentUpdater->update($this->EquipmentIDsNew, $this->EquipmentIDsOld);
		}
	}
	
	/**
	 * Update tag
	 */
	protected function updateTag() {
	    
		if (!empty($this->TagIDsNew) || !empty($this->TagIDsOld)) {
                    $AddNewTags = new Model\Tag\ChosenInserter($this->PDO, $this->TagIDsNew);
                    $AddNewTags->insertTags();
                    $this->TagIDsNew = $AddNewTags->getNewTagIDs();
		    $TagUpdater = new TagUpdater($this->PDO, $this->NewObject->id());
		    $TagUpdater->update($this->TagIDsNew, $this->TagIDsOld);
		}
	}

	/**
	 * Update start time
	 */
	protected function updateStartTime() {
		if ($this->hasChanged(Entity::TIMESTAMP)) {
			if ($this->NewObject->timestamp() < Configuration::Data()->startTime()) {
				Configuration::Data()->updateStartTime($this->NewObject->timestamp());
			} elseif ($this->knowsOldObject() && $this->OldObject->timestamp() == Configuration::Data()->startTime()) {
				Configuration::Data()->recalculateStartTime();
			}
		}
	}

	/**
	 * Update vdot shape and corrector
	 */
	protected function updateVDOTshapeAndCorrector() {
		$timestampLimit = time() - Configuration::Vdot()->days() * DAY_IN_S;

		if (
			(
				$this->hasChanged(Entity::USE_VDOT) &&
				(
					$this->NewObject->timestamp() >= $timestampLimit ||
					($this->knowsOldObject() && $this->OldObject->timestamp() > $timestampLimit)
				)
			) ||
			(
				$this->NewObject->usesVDOT() &&
				(
					$this->hasChanged(Entity::VDOT) ||
					$this->hasChanged(Entity::VDOT_WITH_ELEVATION) ||
					(
						$this->hasChanged(Entity::TIMESTAMP) &&
						$this->knowsOldObject() &&
						(
							($this->NewObject->timestamp() >= $timestampLimit && $this->OldObject->timestamp() < $timestampLimit) ||
							($this->NewObject->timestamp() < $timestampLimit && $this->OldObject->timestamp() >= $timestampLimit)
						)
					)
				)
			)
		) {
			Configuration::Data()->recalculateVDOTshape();
		}

		if (
			(
				$this->NewObject->usesVDOT() ||
				$this->hasChanged(Entity::USE_VDOT)
			) &&
			(
				$this->NewObject->typeid() == Configuration::General()->competitionType() ||
				($this->knowsOldObject() && $this->OldObject->typeid() == Configuration::General()->competitionType())
			)
		) {
			Configuration::Data()->recalculateVDOTcorrector();
		}
	}

	/**
	 * Update basic endurance
	 */
	protected function updateBasicEndurance() {
		$timestampLimit = time() - 182 * DAY_IN_S;

		if (
			(
				$this->hasChanged(Entity::DISTANCE) ||
				$this->hasChanged(Entity::TIMESTAMP) ||
				$this->hasChanged(Entity::SPORTID)
			) && (
				($this->NewObject->sportid() == Configuration::General()->runningSport() && $this->NewObject->timestamp() > $timestampLimit) ||
				($this->knowsOldObject() && $this->OldObject->sportid() == Configuration::General()->runningSport() && $this->OldObject->timestamp() > $timestampLimit)
			)
		) {
			BasicEndurance::recalculateValue();
		}
	}
}