<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

use Runalyze\Model;
use Runalyze\Calculation\BasicEndurance;
use Runalyze\Configuration;

/**
 * Insert activity to database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $Object;

	/**
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Trackdata = null;

	/**
	 * @var \Runalyze\Model\Swimdata\Entity
	 */
	protected $Swimdata = null;
        
	/**
	 * @var \Runalyze\Model\Route\Entity
	 */
	protected $Route = null;

	/**
	 * @var array
	 */
	protected $EquipmentIDs = array();
	
	/**
	 * @var array
	 */
	protected $TagIDs = array();

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Activity\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 */
	public function setTrackdata(Model\Trackdata\Entity $trackdata) {
		$this->Trackdata = $trackdata;
	}
        
	/**
	 * @param \Runalyze\Model\Swimdata\Entity $swimdata
	 */
	public function setSwimdata(Model\Swimdata\Entity $swimdata) {
		$this->Swimdata = $swimdata;
	}

	/**
	 * @param \Runalyze\Model\Route\Entity $route
	 */
	public function setRoute(Model\Route\Entity $route) {
		$this->Route = $route;
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
	 * Tasks before insertion
	 */
	protected function before() {
		parent::before();

		$this->Object->set(Entity::TIMESTAMP_CREATED, time());

		$this->setSportIdIfEmpty();
		$this->removeDataIfInside();
		$this->calculateCaloriesIfZero();
		$this->calculateVDOTAndIntensityAndTrimp();
		$this->calculatePower();
		$this->calculateStrideLength();
		$this->calculateVerticalRatio();
		$this->calculateSwimValues();
	}

	/**
	 * Remove data as weather, if sport is always inside
	 */
	protected function removeDataIfInside() {
		if ($this->Object->sportid() > 0) {
			$Factory = \Runalyze\Context::Factory();

			if (!$Factory->sport($this->Object->sportid())->isOutside()) {
				$this->Object->weather()->condition()->set( \Runalyze\Data\Weather\Condition::UNKNOWN );
				$this->Object->weather()->temperature()->setTemperature(null);

				$this->Object->synchronize();
			}
		}
	}

	/**
	 * Set sport id if empty
	 */
	protected function setSportIdIfEmpty() {
		if ($this->Object->sportid() == 0) {
			$this->Object->set(Entity::SPORTID, Configuration::General()->mainSport());
		}
	}

	/**
	 * Calculate calories if empty
	 */
	protected function calculateCaloriesIfZero() {
		if ($this->Object->calories() == 0 && $this->Object->sportid() > 0) {
			$Factory = \Runalyze\Context::Factory();
			$calories = $Factory->sport($this->Object->sportid())->caloriesPerHour() * $this->Object->duration() / 3600;

			$this->Object->set(Entity::CALORIES, $calories);
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
			$this->Object->set(Entity::VDOT_BY_TIME, $Calculator->calculateVDOTbyTime());
			$this->Object->set(Entity::VDOT, $Calculator->calculateVDOTbyHeartRate());
			$this->Object->set(Entity::VDOT_WITH_ELEVATION, $Calculator->calculateVDOTbyHeartRateWithElevation());
			$this->Object->set(Entity::JD_INTENSITY, $Calculator->calculateJDintensity());

			if (class_exists('RunalyzePluginPanel_Rechenspiele') && $this->Object->timestamp() > time() - 14*DAY_IN_S) {
				\Cache::delete(\RunalyzePluginPanel_Rechenspiele::CACHE_KEY_JD_POINTS);
			}
		} else {
			$this->Object->unsetRunningValues();
		}

		$this->Object->set(Entity::TRIMP, $Calculator->calculateTrimp());
	}

	/**
	 * Calculate power
	 */
	protected function calculatePower() {
		if (
			\Runalyze\Context::Factory()->sport($this->Object->sportid())->hasPower() &&
			Configuration::ActivityForm()->computePower() &&
			(null !== $this->Trackdata)
		) {
			$Calculator = new \Runalyze\Calculation\Power\Calculator(
				$this->Trackdata,
				$this->Route
			);
			$Calculator->calculate();

			$this->Trackdata->set(Model\Trackdata\Entity::POWER, $Calculator->powerData());
			$this->Object->set(Entity::POWER, $Calculator->average());
		}
	}

	/**
	 * Calculate stride length
	 */
	protected function calculateStrideLength() {
		if ($this->Object->sportid() == Configuration::General()->runningSport()) {
			$this->Object->set(Entity::STRIDE_LENGTH, \Runalyze\Calculation\StrideLength\Calculator::forActivity($this->Object));
		}
	}

	/**
	 * Calculate vertical ratio
	 */
	protected function calculateVerticalRatio() {
		if ($this->Object->sportid() == Configuration::General()->runningSport()) {
			$this->Object->set(Entity::VERTICAL_RATIO, \Runalyze\Calculation\Activity\VerticalRatioCalculator::forActivity($this->Object));
		}
	}

	/**
	 * Calculate swim values
	 */
	protected function calculateSwimValues() {
		if (null !== $this->Trackdata && null !== $this->Swimdata) {
			if ($this->Swimdata->stroke()) {
				$this->Object->set(Entity::TOTAL_STROKES, array_sum($this->Swimdata->stroke()));
			}

			if ($this->Object->totalStrokes() && $this->Trackdata->totalTime()) {
			   $num = $this->Trackdata->num();
			   $totaltime = $this->Trackdata->totalTime();
			   $totalstrokes = $this->Object->totalStrokes();

				if (!empty($totalstrokes) && !empty($totaltime) & !empty($num) && $totalstrokes != 0) {
					$this->Object->set(Entity::SWOLF, round(($totalstrokes + $totaltime) / $num));
				}
			}
		}
	}
        
	/**
	 * Tasks after insertion
	 */
	protected function after() {
		$this->updateEquipment();
		$this->updateTag();
		$this->updateStartTime();
		$this->updateVDOTshapeAndCorrector();
		$this->updateBasicEndurance();
	}

	/**
	 * Update tag
	 */
	protected function updateTag() {
	    if (!empty($this->TagIDs)) {
                $AddNewTags = new Model\Tag\ChosenInserter($this->PDO, $this->TagIDs);
                $AddNewTags->insertTags();
                $this->TagIDs = $AddNewTags->getNewTagIDs();
		$TagUpdater = new TagUpdater($this->PDO, $this->Object->id());
		    $TagUpdater->update($this->TagIDs);
	    }
	}
	/**
	 * Update equipment
	 */
	protected function updateEquipment() {
		if (!empty($this->EquipmentIDs)) {
	        $EquipmentUpdater = new EquipmentUpdater($this->PDO, $this->Object->id());
			$EquipmentUpdater->setActivityObjects($this->Object);
			$EquipmentUpdater->update($this->EquipmentIDs);
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