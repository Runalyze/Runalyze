<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

use Runalyze\Model;
use Runalyze\Configuration;

use BasicEndurance;

/**
 * Insert activity to database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $Object;

	/**
	 * @var \Runalyze\Model\Trackdata\Object
	 */
	protected $Trackdata = null;

	/**
	 * @var \Runalyze\Model\Swim\Object
	 */
	protected $Swimdata = null;
        
	/**
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $Route = null;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Activity\Object $object [optional]
	 */
	public function __construct(\PDO $connection, Object $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 */
	public function setTrackdata(Model\Trackdata\Object $trackdata) {
		$this->Trackdata = $trackdata;
	}
        
	/**
	 * @param \Runalyze\Model\Swim\Object $swimdata
	 */
	public function setSwimdata(Model\Swimdata\Object $swimdata) {
		$this->Swimdata = $swimdata;
	}

	/**
	 * @param \Runalyze\Model\Route\Object $route
	 */
	public function setRoute(Model\Route\Object $route) {
		$this->Route = $route;
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
			Object::allDatabaseProperties()
		);
	}

	/**
	 * Tasks before insertion
	 */
	protected function before() {
		parent::before();

		$this->Object->set(Object::TIMESTAMP_CREATED, time());

		$this->removeDataIfInside();
		$this->calculateCaloriesIfZero();
		$this->calculateVDOTAndIntensityAndTrimp();
		$this->calculatePower();
		$this->calculateStrideLength();
		$this->calculateSwimValues();
	}

	/**
	 * Remove data as weather/clothes, if sport is always inside
	 */
	protected function removeDataIfInside() {
		if ($this->Object->sportid() > 0) {
			$Factory = \Runalyze\Context::Factory();

			if (!$Factory->sport($this->Object->sportid())->isOutside()) {
				$this->Object->weather()->condition()->set( \Runalyze\Data\Weather\Condition::UNKNOWN );
				$this->Object->weather()->temperature()->setTemperature(null);
				$this->Object->clothes()->clear();

				$this->Object->synchronize();
			}
		}
	}

	/**
	 * Calculate calories if empty
	 */
	protected function calculateCaloriesIfZero() {
		if ($this->Object->calories() == 0 && $this->Object->sportid() > 0) {
			$Factory = \Runalyze\Context::Factory();
			$calories = $Factory->sport($this->Object->sportid())->caloriesPerHour() * $this->Object->duration() / 3600;

			$this->Object->set(Object::CALORIES, $calories);
		}
	}

	/**
	 * Calculate VDOT, jd intensity, TRIMP
	 */
	protected function calculateVDOTAndIntensityAndTrimp() {
		$Calculator = new \Runalyze\Calculation\Activity\Calculator(
			$this->Object,
			$this->Trackdata,
			$this->Route
		);

		if ($this->Object->sportid() == Configuration::General()->runningSport()) {
			$this->Object->set(Object::VDOT_BY_TIME, $Calculator->calculateVDOTbyTime());
			$this->Object->set(Object::VDOT, $Calculator->calculateVDOTbyHeartRate());
			$this->Object->set(Object::VDOT_WITH_ELEVATION, $Calculator->calculateVDOTbyHeartRateWithElevation());
			$this->Object->set(Object::JD_INTENSITY, $Calculator->calculateJDintensity());

			if (class_exists('RunalyzePluginPanel_Rechenspiele') && $this->Object->timestamp() > time() - 14*DAY_IN_S) {
				\Cache::delete(\RunalyzePluginPanel_Rechenspiele::CACHE_KEY_JD_POINTS);
			}
		} else {
			$this->Object->unsetRunningValues();
		}

		$this->Object->set(Object::TRIMP, $Calculator->calculateTrimp());
	}

	/**
	 * Calculate power
	 */
	protected function calculatePower() {
		if (
			\Runalyze\Context::Factory()->sport($this->Object->sportid())->hasPower() &&
			Configuration::ActivityForm()->computePower() &&
			(NULL !== $this->Trackdata)
		) {
			$Calculator = new \Runalyze\Calculation\Power\Calculator(
				$this->Trackdata,
				$this->Route
			);
			$Calculator->calculate();

			$this->Trackdata->set(Model\Trackdata\Object::POWER, $Calculator->powerData());
			$this->Object->set(Object::POWER, $Calculator->average());
		}
	}

	/**
	 * Calculate stride length
	 */
	protected function calculateStrideLength() {
		if ($this->Object->sportid() == Configuration::General()->runningSport()) {
			if (NULL !== $this->Trackdata && $this->Trackdata->has(Model\Trackdata\Object::CADENCE)) {
				$Calculator = new \Runalyze\Calculation\StrideLength\Calculator($this->Trackdata);
				$Calculator->calculate();

				$this->Object->set(Object::STRIDE_LENGTH, $Calculator->average());
			} elseif ($this->Object->cadence() > 0) {
				$this->Object->set(Object::STRIDE_LENGTH, \Runalyze\Calculation\StrideLength\Calculator::forActivity($this->Object));
			}
		}
	}

	/**
	 * Calculate swim values
	 */
	protected function calculateSwimValues() {
		if (NULL !== $this->Trackdata && NULL !== $this->Swimdata) {
			if ($this->Swimdata->stroke()) {
				$this->Object->set(Object::TOTAL_STROKES, array_sum($this->Swimdata->stroke()));
			}

			if ($this->Object->totalStrokes() && $this->Trackdata->totalTime()) {
			   $num = $this->Trackdata->num();
			   $totaltime = $this->Trackdata->totalTime();
			   $totalstrokes = $this->Object->totalStrokes();

				if (!empty($totalstrokes) && !empty($totaltime) & !empty($num) && $totalstrokes != 0) {
					$this->Object->set(Object::SWOLF, round(($totalstrokes + $totaltime) / $num));
				}
			}
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
		if ($this->Object->shoeID() > 0) {
			$this->PDO->exec(
				'UPDATE `'.PREFIX.'shoe` SET
					`km` = `km` + '.(float)$this->Object->distance().',
					`time` = `time` + '.(int)$this->Object->duration().'
				WHERE `id`="'.$this->Object->shoeID().'" LIMIT 1'
			);
		}
	}

	/**
	 * Update start time
	 */
	protected function updateStartTime() {
		if ($this->Object->timestamp() < Configuration::Data()->startTime()) {
			Configuration::Data()->updateStartTime($this->Object->timestamp());
		}
	}

	/**
	 * Update vdot shape and corrector
	 */
	protected function updateVDOTshapeAndCorrector() {
		$timestampLimit = time() - Configuration::Vdot()->days() * DAY_IN_S;

		if (
			$this->Object->usesVDOT() &&
			$this->Object->vdotByHeartRate() > 0 &&
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

		if ($this->Object->sportid() == Configuration::General()->runningSport() && $this->Object->timestamp() > $timestampLimit) {
			BasicEndurance::recalculateValue();
		}
	}
}