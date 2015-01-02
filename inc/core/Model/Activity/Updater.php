<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

use Runalyze\Model;
use Runalyze\Configuration;

use BasicEndurance;

/**
 * Update activity in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class Updater extends Model\UpdaterWithIDAndAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $NewObject;

	/**
	 * @var \Runalyze\Model\Trackdata\Object
	 */
	protected $Trackdata = null;

	/**
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $Route = null;

	/**
	 * @var boolean
	 */
	protected $ForceRecalculations = false;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Activity\Object $newObject [optional]
	 * @param \Runalyze\Model\Activity\Object $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Object $newObject = null, Object $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 */
	public function setTrackdata(Model\Trackdata\Object $trackdata) {
		$this->Trackdata = $trackdata;
	}

	/**
	 * @param \Runalyze\Model\Route\Object $route
	 */
	public function setRoute(Model\Route\Object $route) {
		$this->Route = $route;
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
			Object::allProperties()
		);
	}

	/**
	 * Ignore specific key
	 * @param type $key
	 * @return boolean
	 */
	protected function ignore($key) {
		if ($key == Object::DISTANCE || $key == Object::TIME_IN_SECONDS) {
			if ($this->OldObject == null && $this->NewObject->shoeID() > 0) {
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

		$this->NewObject->set(Object::TIMESTAMP_EDITED, time());

		$this->updateVDOTAndIntensityAndTrimp();
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
			if ($this->ForceRecalculations || $this->hasChanged(Object::TIME_IN_SECONDS) || $this->hasChanged(Object::DISTANCE) || $this->hasChanged(Object::HR_AVG)) {
				$this->NewObject->set(Object::VDOT_BY_TIME, $Calculator->calculateVDOTbyTime());
				$this->NewObject->set(Object::JD_INTENSITY, $Calculator->calculateJDintensity());
				$this->NewObject->set(Object::VDOT, $Calculator->calculateVDOTbyHeartRate());
				$this->NewObject->set(Object::VDOT_WITH_ELEVATION, $Calculator->calculateVDOTbyHeartRateWithElevation());
			}
		} else {
			$this->NewObject->unsetRunningValues();
		}

		if ($this->ForceRecalculations || $this->hasChanged(Object::TIME_IN_SECONDS) || $this->hasChanged(Object::HR_AVG)) {
			$this->NewObject->set(Object::TRIMP, $Calculator->calculateTrimp());
		}
	}

	/**
	 * Tasks after insertion
	 */
	protected function after() {
		$this->updateEquipment();
		$this->updateStartTime();
		$this->updateVDOTshapeAndCorrector();
		$this->updateBasicEndurance();
	}

	/**
	 * Update equipment
	 */
	protected function updateEquipment() {
		if ($this->hasChanged(Object::SHOEID)) {
			if ($this->knowsOldObject()) {
				$this->PDO->exec(
					'UPDATE `'.PREFIX.'shoe` SET
						`km` = `km` - '.(float)$this->OldObject->distance().',
						`time` = `time` - '.(int)$this->OldObject->duration().'
					WHERE `id`="'.$this->OldObject->shoeID().'" LIMIT 1'
				);
			}

			$this->PDO->exec(
				'UPDATE `'.PREFIX.'shoe` SET
					`km` = `km` + '.(float)$this->NewObject->distance().',
					`time` = `time` + '.(int)$this->NewObject->duration().'
				WHERE `id`="'.$this->NewObject->shoeID().'" LIMIT 1'
			);
		} elseif ($this->hasChanged(Object::DISTANCE) || $this->hasChanged(Object::TIME_IN_SECONDS)) {
			$this->PDO->exec(
				'UPDATE `'.PREFIX.'shoe` SET
					`km` = `km` + ('.((float)$this->NewObject->distance() - (float)$this->OldObject->distance()).'),
					`time` = `time` + ('.((int)$this->NewObject->duration() - (float)$this->OldObject->duration()).')
				WHERE `id`="'.$this->NewObject->shoeID().'" LIMIT 1'
			);
		}
	}

	/**
	 * Update start time
	 */
	protected function updateStartTime() {
		if ($this->hasChanged(Object::TIMESTAMP)) {
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
			$this->hasChanged(Object::VDOT) ||
			$this->hasChanged(Object::VDOT_WITH_ELEVATION) ||
			($this->hasChanged(Object::TIMESTAMP) &&
				$this->NewObject->timestamp() > $timestampLimit ||
				($this->knowsOldObject() && $this->OldObject->timestamp() > $timestampLimit)
			)
		) {
			Configuration::Data()->recalculateVDOTshape();

			if (
				$this->NewObject->typeid() == Configuration::General()->competitionType() ||
				($this->knowsOldObject() && $this->OldObject->typeid() == Configuration::General()->competitionType())
			) {
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
			(
				$this->hasChanged(Object::DISTANCE) ||
				$this->hasChanged(Object::TIMESTAMP) ||
				$this->hasChanged(Object::SPORTID)
			) && (
				($this->NewObject->sportid() == Configuration::General()->runningSport() && $this->NewObject->timestamp() > $timestampLimit) ||
				($this->knowsOldObject() && $this->OldObject->sportid() == Configuration::General()->runningSport() && $this->OldObject->timestamp() > $timestampLimit)
			)
		) {
			BasicEndurance::recalculateValue();
		}
	}
}