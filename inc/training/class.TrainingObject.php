<?php
/**
 * This file contains class::TrainingObject
 * @package Runalyze\DataObjects\Training
 */

use Runalyze\Configuration;
use Runalyze\Util\LocalTime;
use Runalyze\Util\Time;
use Runalyze\Data\Cadence;

/**
 * DataObject for trainings
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training
 */
class TrainingObject extends DataObject {
	/**
	 * Sport
	 * @var \Sport
	 */
	private $Sport = null;

	/**
	 * Weather
	 * @var \Runalyze\Data\Weather
	 */
	private $Weather = null;

	/**
	 * Splits
	 * @var \Splits
	 */
	private $Splits = null;

	/**
	 * Cadence
	 * @var \Runalyze\Data\Cadence\AbstractCadence
	 */
	private $Cadence = null;


	/**
	 * Pauses
	 * @var \Runalyze\Model\Trackdata\Pauses
	 */
	private $Pauses = null;

	/**
	 * Fill default object with standard settings
	 */
	protected function fillDefaultObject() {
		$this->set('time', isset($_GET['date']) ? LocalTime::fromString($_GET['date'])->getTimestamp() : LocalTime::fromServerTime(time())->setTime(0, 0, 0)->getTimestamp());
		$this->set('is_public', Configuration::Privacy()->publishActivity() ? '1' : '0');
		$this->set('sportid', Configuration::General()->mainSport());
		$this->forceToSet('s_sum_with_distance', 0);
		$this->forceToSet('importer-equipment', []);
	}

	/**
	 * Set weather forecast
	 */
	public function setWeatherForecast() {
		if (!Configuration::ActivityForm()->loadWeather())
			return;

		$Location = new \Runalyze\Data\Weather\Location();
		$Location->setTimestamp($this->getTimestamp());
		$Location->setLocationName(Configuration::ActivityForm()->weatherLocation());

		if ($this->hasPositionData()) {
			$Location->setPosition($this->getFirstNonEmptyArrayPoint('arr_lat'), $this->getFirstNonEmptyArrayPoint('arr_lon'));
		}

		$Forecast = new \Runalyze\Service\WeatherForecast\Forecast($Location);
		$Weather = $Forecast->object();
		$Weather->temperature()->toCelsius();

		$this->set('weatherid', $Weather->condition()->id());
		$this->set('temperature', $Weather->temperature()->value());
		$this->set('wind_speed', $Weather->windSpeed()->value());
		$this->set('wind_deg', $Weather->windDegree()->value());
		$this->set('humidity', $Weather->humidity()->value());
		$this->set('pressure', $Weather->pressure()->value());
		$this->set('weather_source', $Weather->source());
	}

	/**
	 * Init DatabaseScheme
	 */
	protected function initDatabaseScheme() {
		$this->DatabaseScheme = DatabaseSchemePool::get('training/schemes/scheme.Training.php');
	}

	/**
	 * Set all internal values as post data
	 */
	final public function setValuesAsPostData() {
		$this->updateAfterParsing();

		parent::setValuesAsPostData();
	}

	/**
	 * Update internal array after parsing
	 */
	final public function updateAfterParsing() {
		$this->removeSingleSplits();
		$this->setSplitsFromObject();
		$this->setPausesFromObject();
		$this->loadRouteName();
	}

	/**
	 * Load route name
	 */
	private function loadRouteName() {
		if ($this->hasProperty('routeid') && $this->get('routeid') > 0) {
			$name = DB::getInstance()->query('SELECT `name` FROM `'.PREFIX.'route` WHERE `id`="'.$this->get('routeid').'" AND `accountid` = '.SessionAccountHandler::getId().' LIMIT 1')->fetchColumn();
			$this->set('route', $name);
		}
	}

	/**
	 * Insert object to database
	 */
	public function insert() {
		$DB = DB::getInstance();
		$AccountID = SessionAccountHandler::getId();
		$Route = $this->newRouteObject();
		$Trackdata = $this->newTrackdataObject();
		$Swimdata = $this->newSwimObject();

		if ($Route->name() != '' || $Route->hasPositionData() || $Route->hasElevations()) {
			$InserterRoute = new Runalyze\Model\Route\Inserter($DB, $Route);
			$InserterRoute->setAccountID($AccountID);
			$InserterRoute->insert();

			$this->forceToSet('routeid', $InserterRoute->insertedID());

			if ($this->getElevation() == 0) {
				$this->setElevation($Route->elevation());
			}
		}

		$Activity = $this->newActivityObject();

		$InserterActivity = new Runalyze\Model\Activity\Inserter($DB, $Activity);
		$InserterActivity->setAccountID($AccountID);
		$InserterActivity->setRoute($Route);
		$InserterActivity->setTrackdata($Trackdata);
		$InserterActivity->setSwimdata($Swimdata);

		if ($this->get('importer-equipment') != []) {
			$InserterActivity->setEquipmentIDs($this->get('importer-equipment'));
		} else {
			$InserterActivity->setEquipmentIDs(TrainingFormular::readEquipmentFromPost($Activity->sportid()));
		}

		$InserterActivity->setTagIDs(TrainingFormular::readTagFromPost());
		$InserterActivity->insert();

		$this->id = $InserterActivity->insertedID();

		if ($this->hasArrayTime() || $this->hasArrayDistance() || $this->hasArrayHeartrate()) {
			$Trackdata->set(Runalyze\Model\Trackdata\Entity::ACTIVITYID, $this->id());
			$InserterTrack = new Runalyze\Model\Trackdata\Inserter($DB, $Trackdata);
			$InserterTrack->setAccountID($AccountID);
			$InserterTrack->insert();
		}

		if ($this->hasArrayStroke() || $this->hasArrayStrokeType() ) {
			$Swimdata->set(Runalyze\Model\Swimdata\Entity::ACTIVITYID, $this->id());
			$InserterSwim = new Runalyze\Model\Swimdata\Inserter($DB, $Swimdata);
			$InserterSwim->setAccountID($AccountID);
			$InserterSwim->insert();
		}

		if ($this->hasArrayHRV()) {
			$HRV = $this->newHRVObject();
			$HRV->set(Runalyze\Model\HRV\Entity::ACTIVITYID, $this->id());
			$InserterHRV = new Runalyze\Model\HRV\Inserter($DB, $HRV);
			$InserterHRV->setAccountID($AccountID);
			$InserterHRV->insert();
		}
	}

    /**
     * Insert object to database
     */
    public function mergeInto(Runalyze\Model\Activity\Entity $otherActivity, $accountId) {
        $DB = DB::getInstance();
        $Route = $this->newRouteObject();
        $Trackdata = $this->newTrackdataObject();
        $Swimdata = $this->newSwimObject();

        $oldRoute = (new Runalyze\Model\Factory($accountId))->route($otherActivity->get('routeid'));

        if ($oldRoute->isEmpty()) {
            if ($Route->name() != '' || $Route->hasPositionData() || $Route->hasElevations()) {
                $InserterRoute = new Runalyze\Model\Route\Inserter($DB, $Route);
                $InserterRoute->setAccountID($accountId);
                $InserterRoute->insert();

                $this->forceToSet('routeid', $InserterRoute->insertedID());

                if ($this->getElevation() == 0) {
                    $this->setElevation($Route->elevation());
                }
            }
        } else {
            $Route->setID($oldRoute->id());
            $Route->set('name', $oldRoute->name());
            $Route->set('cities', $oldRoute->name());
            $routeUpdater = new Runalyze\Model\Route\Updater($DB, $Route, $oldRoute);
            $routeUpdater->setAccountID($accountId);
            $routeUpdater->update();

            if ($this->getElevation() == 0) {
                $this->setElevation($Route->elevation());
            }
        }

        $this->setSportid($otherActivity->sportid());
        $newActivity = $this->newActivityObject();
        $newActivityInserter = new Runalyze\Model\Activity\Inserter($DB, $newActivity);
        $newActivityInserter->setAccountID($accountId);
        $newActivityInserter->setRoute($Route);
        $newActivityInserter->setTrackdata($Trackdata);
        $newActivityInserter->setSwimdata($Swimdata);
        $newActivityInserter->before();

        $newActivity->setID($otherActivity->id());
        $newActivity->set('created', $otherActivity->get('created'));
        $newActivity->weather()->condition()->set($otherActivity->weather()->condition()->id());
        $newActivity->weather()->temperature()->setTemperature($otherActivity->weather()->temperature()->value());
        $newActivity->synchronize();

        foreach ($newActivity->properties() as $key) {
            if (!$newActivity->has($key) && $otherActivity->has($key)) {
                if ('partner' == $key) {
                    $newActivity->partner()->fromString($otherActivity->partner()->asString());
                } else {
                    $newActivity->set($key, $otherActivity->get($key));
                }
            }
        }

        $activityUpdater = new Runalyze\Model\Activity\Updater($DB, $newActivity, $otherActivity);
        $activityUpdater->setAccountID($accountId);
        $activityUpdater->setTrackdata($Trackdata);
        $activityUpdater->setRoute($Route);
        $activityUpdater->update();

        if ($this->hasArrayTime() || $this->hasArrayDistance() || $this->hasArrayHeartrate()) {
            $Trackdata->set(Runalyze\Model\Trackdata\Entity::ACTIVITYID, $otherActivity->id());
            $InserterTrack = new Runalyze\Model\Trackdata\Inserter($DB, $Trackdata);
            $InserterTrack->setAccountID($accountId);
            $InserterTrack->insert();
        }

        if ($this->hasArrayStroke() || $this->hasArrayStrokeType() ) {
            $Swimdata->set(Runalyze\Model\Swimdata\Entity::ACTIVITYID, $otherActivity->id());
            $InserterSwim = new Runalyze\Model\Swimdata\Inserter($DB, $Swimdata);
            $InserterSwim->setAccountID($accountId);
            $InserterSwim->insert();
        }

        if ($this->hasArrayHRV()) {
            $HRV = $this->newHRVObject();
            $HRV->set(Runalyze\Model\HRV\Entity::ACTIVITYID, $otherActivity->id());
            $InserterHRV = new Runalyze\Model\HRV\Inserter($DB, $HRV);
            $InserterHRV->setAccountID($accountId);
            $InserterHRV->insert();
        }
    }

	/**
	 * @return \Runalyze\Model\Activity\Entity
	 */
	protected function newActivityObject() {
		return new Runalyze\Model\Activity\Entity($this->data);
	}

	/**
	 * @return \Runalyze\Model\Route\Entity
	 */
	protected function newRouteObject() {
		$Route = new Runalyze\Model\Route\Entity(array(
			Runalyze\Model\Route\Entity::NAME => $this->get('route'),
			Runalyze\Model\Route\Entity::CITIES => $this->get('route'),
			Runalyze\Model\Route\Entity::DISTANCE => $this->get('distance'),
			Runalyze\Model\Route\Entity::ELEVATIONS_ORIGINAL => $this->get('arr_alt')
		));

		if ($this->hasArrayLatitude()) {
			$Route->setLatitudesLongitudes($this->getArrayFor('arr_lat'), $this->getArrayFor('arr_lon'));
		}

		return $Route;
	}

	/**
	 * @return \Runalyze\Model\Swimdata\Entity
	 */
	protected function newSwimObject() {
		return new Runalyze\Model\Swimdata\Entity(array(
			Runalyze\Model\Swimdata\Entity::STROKE => $this->get('stroke'),
                        Runalyze\Model\Swimdata\Entity::STROKETYPE => $this->get('stroketype'),
                        Runalyze\Model\Swimdata\Entity::POOL_LENGTH => $this->get('pool_length')
		));

	}

	/**
	 * @return \Runalyze\Model\Trackdata\Entity
	 */
	protected function newTrackdataObject() {
		return new Runalyze\Model\Trackdata\Entity(array(
			Runalyze\Model\Trackdata\Entity::TIME => $this->get('arr_time'),
			Runalyze\Model\Trackdata\Entity::DISTANCE => $this->get('arr_dist'),
			Runalyze\Model\Trackdata\Entity::HEARTRATE => $this->get('arr_heart'),
			Runalyze\Model\Trackdata\Entity::CADENCE => $this->get('arr_cadence'),
			Runalyze\Model\Trackdata\Entity::POWER => $this->get('arr_power'),
			Runalyze\Model\Trackdata\Entity::TEMPERATURE => $this->get('arr_temperature'),
			Runalyze\Model\Trackdata\Entity::GROUNDCONTACT => $this->get('arr_groundcontact'),
			Runalyze\Model\Trackdata\Entity::VERTICAL_OSCILLATION => $this->get('arr_vertical_oscillation'),
			Runalyze\Model\Trackdata\Entity::GROUNDCONTACT_BALANCE => $this->get('arr_groundcontact_balance'),
            Runalyze\Model\Trackdata\Entity::SMO2_0 => $this->get('arr_smo2_0'),
            Runalyze\Model\Trackdata\Entity::SMO2_1  => $this->get('arr_smo2_1'),
            Runalyze\Model\Trackdata\Entity::THB_0 => $this->get('arr_thb_0'),
            Runalyze\Model\Trackdata\Entity::THB_1 => $this->get('arr_thb_1'),
			Runalyze\Model\Trackdata\Entity::PAUSES => $this->get('pauses')
		));
	}

	/**
	 * @return \Runalyze\Model\HRV\Entity
	 */
	protected function newHRVObject() {
		return new Runalyze\Model\HRV\Entity(array(
			Runalyze\Model\HRV\Entity::DATA => $this->get('hrv')
		));
	}

	/**
	 * Update object in database
	 */
	public function update() {
		$DB = DB::getInstance();
		$AccountID = SessionAccountHandler::getId();
		$OldData = isset($_POST['old-data'])
				? unserialize(base64_decode($_POST['old-data']))
				: array();

		if (!is_array($OldData)) {
			$OldData = array();
		}

		$NewActivity = $this->newActivityObject();
		$UpdaterActivity = new \Runalyze\Model\Activity\Updater($DB,
			$NewActivity,
			new \Runalyze\Model\Activity\Entity($OldData)
		);

		if (isset($OldData['routeid']) && $OldData['routeid'] > 0) {
			$UpdaterActivity->setRoute(\Runalyze\Context::Factory()->route($OldData['routeid']));
		} elseif ($this->get('route') != '') {
			$InserterRoute = new Runalyze\Model\Route\Inserter($DB, new Runalyze\Model\Route\Entity(array(
				Runalyze\Model\Route\Entity::NAME => $this->get('route'),
				Runalyze\Model\Route\Entity::CITIES => $this->get('route'),
				Runalyze\Model\Route\Entity::DISTANCE => $this->get('distance')
			)));
			$InserterRoute->setAccountID($AccountID);
			$InserterRoute->insert();

			$NewActivity->set(Runalyze\Model\Activity\Entity::ROUTEID, $InserterRoute->insertedID());
		}

		$UpdaterActivity->setTrackdata(\Runalyze\Context::Factory()->trackdata($this->id()));
		$UpdaterActivity->setEquipmentIDs(
			TrainingFormular::readEquipmentFromPost($NewActivity->sportid()),
			isset($_POST['equipment_old']) ? explode(',', $_POST['equipment_old']) : array()
		);
		$UpdaterActivity->setTagIDs(TrainingFormular::readTagFromPost(),
			isset($_POST['tag_old']) ? explode(',', $_POST['tag_old']) : array());
		$UpdaterActivity->setAccountID($AccountID);
		$UpdaterActivity->update();

		if (isset($OldData['routeid']) && isset($OldData['route'])) {
			$UpdaterRoute = new \Runalyze\Model\Route\Updater($DB,
				new Runalyze\Model\Route\Entity(array(
					'id' => $OldData['routeid'],
					Runalyze\Model\Route\Entity::NAME => $this->get('route'),
					Runalyze\Model\Route\Entity::CITIES => $this->get('route')
				)),
				new Runalyze\Model\Route\Entity(array(
					'id' => $OldData['routeid'],
					Runalyze\Model\Route\Entity::NAME => $OldData['route'],
					Runalyze\Model\Route\Entity::CITIES => $OldData['route']
				))
			);
			$UpdaterRoute->setAccountID($AccountID);
			$UpdaterRoute->update();
		}
	}


	/**************************************************************************
	 *
	 * The following methods serve simple get/set-methods for internal objects
	 *
	 *************************************************************************/

	/**
	 * Average heartfrequence
	 * @return int
	 */
	public function avgHF() {
		if ($this->getPulseAvg() > 0)
			return $this->getPulseAvg();

		return $this->Sport()->avgHF();
	}


	/**************************************************************************
	 *
	 * The following methods serve simple get/set-methods for internal objects
	 *
	 *************************************************************************/

	/**
	 * Sport object
	 * @return \Sport
	 */
	public function Sport() {
		if (is_null($this->Sport))
			$this->Sport = new Sport($this->get('sportid'));

		return $this->Sport;
	}

	/**
	 * Weather object
	 * @return \Runalyze\Data\Weather
	 */
	public function Weather() {
		if (is_null($this->Weather)) {
			$id   = ($this->hasProperty('weatherid')) ? $this->get('weatherid') : \Runalyze\Profile\Weather\WeatherConditionProfile::UNKNOWN;
			$temp = ($this->hasProperty('temperature')) ? $this->get('temperature') : null;
			$windSpeed = ($this->hasProperty('wind_speed')) ? $this->get('wind_speed') : null;
			$windDegree = ($this->hasProperty('wind_deg')) ? $this->get('wind_deg') : null;
			$humidity = ($this->hasProperty('humidity')) ? $this->get('humidity') : null;
			$pressure = ($this->hasProperty('pressure')) ? $this->get('pressure') : null;
			$sourceId = ($this->hasProperty('weather_source')) ? $this->get('weather_source') : null;

			$this->Weather = new \Runalyze\Data\Weather(
				new \Runalyze\Data\Weather\Temperature($temp),
				new \Runalyze\Data\Weather\Condition($id),
				new \Runalyze\Data\Weather\WindSpeed($windSpeed),
				new \Runalyze\Data\Weather\WindDegree($windDegree),
				new \Runalyze\Data\Weather\Humidity($humidity),
				new \Runalyze\Data\Weather\Pressure($pressure)
			);
			$this->Weather->setSource($sourceId);
		}

		return $this->Weather;
	}

	/**
	 * Splits object
	 * @return \Splits
	 */
	public function Splits() {
		if (is_null($this->Splits))
			$this->Splits = new Splits($this->get('splits'));

		return $this->Splits;
	}

	/**
	 * Cadence object
	 * @return \Runalyze\Data\Cadence\AbstractCadence
	 */
	public function Cadence() {
		if (is_null($this->Cadence)) {
			if ($this->Sport()->isRunning())
				$this->Cadence = new Cadence\Running($this->get('cadence'));
			else
				$this->Cadence = new Cadence\General($this->get('cadence'));
		}

		return $this->Cadence;
	}

	/**
	 * Pauses object
	 * @return \Runalyze\Model\Trackdata\Pauses
	 */
	public function Pauses() {
		if (is_null($this->Pauses)) {
			$data = $this->hasProperty('pauses') ? $this->get('pauses') : '';
			$this->Pauses = new Runalyze\Model\Trackdata\Pauses($data);
		}

		return $this->Pauses;
	}

	/**
	 * Set pauses from pauses object
	 */
	public function setPausesFromObject() {
		$this->forceToSet('pauses', $this->Pauses()->asString());
	}


	/**************************************************************************
	 *
	 * The following methods serve simple get/set-methods for all internal
	 * attributes.
	 *
	 *************************************************************************/

	/**
	 * Set sportid
	 * @param int $id sportid
	 */
	public function setSportid($id) { $this->set('sportid', $id); }


	/**
	 * Set typeid
	 * @param int $id typeid
	 */
	public function setTypeid($id) { $this->set('typeid', $id); }
	/**
	 * Get typeid
	 * @return int typeid
	 */
	public function getTypeid() { return $this->get('typeid'); }


	/**
	 * Set shoeid
	 * @param int $id shoeid
	 */
	public function setShoeid($id) { $this->set('importer-equipment', [$id]); }


	/**
	 * Set timestamp
	 * @param int $time timestamp of training
	 */
	public function setTimestamp($time) { $this->set('time', $time); }
	/**
	 * Get timestamp
	 * @return int timestamp of training
	 */
	public function getTimestamp() { return $this->get('time'); }

	/**
	 * Set timezone
	 * @param int $timezone timezone of training
	 */
	public function setTimezoneOffset($timezone) { $this->set('timezone_offset', $timezone); }
	/**
	 * Get timezone
	 * @return int timezone of training
	 */
	public function getTimezoneOffset() { return $this->get('timezone_offset'); }

	/**
	 * Get created-timestamp
	 * @return int timestamp of creation
	 */
	public function getCreatedTimestamp() { return $this->get('created'); }


	/**
	 * Get edited-timestamp
	 * @return int timestamp of last edit
	 */
	public function getEditedTimestamp() { return $this->get('edited'); }


	/**
	 * Set public
	 */
	public function setPublic() { $this->set('is_public', 1); }
	/**
	 * Set private
	 */
	public function setPrivate() { $this->set('is_public', 0); }
	/**
	 * Is public?
	 * @return bool
	 */
	public function isPublic() { return $this->get('is_public') == 1; }


	/**
	 * Set track
	 * @param bool $isTrack Was this training on track?
	 */
	public function setTrack($isTrack) { $this->set('is_track', $isTrack); }
	/**
	 * Is track?
	 * @return bool True if training was on track.
	 */
	public function isTrack() { return $this->get('is_track') == 1; }


	/**
	 * Set distance
	 * @param double $km distance in kilometer
	 */
	public function setDistance($km) { $this->set('distance', $km); }
	/**
	 * Get distance
	 * @return double distance in kilometer
	 */
	public function getDistance() { return $this->get('distance'); }
	/**
	 * Has distance?
	 * @return bool True if training has a (positive) distance.
	 */
	public function hasDistance() { return $this->getDistance() > 0; }


	/**
	 * Set time
	 * @param int $timeInSeconds duration
	 */
	public function setTimeInSeconds($timeInSeconds) { $this->set('s', $timeInSeconds); }
	/**
	 * Get time
	 * @return int duration in seconds
	 */
	public function getTimeInSeconds() { return $this->get('s'); }
	/**
	 * Get time sum with distance
	 * @return int duration in seconds
	 */
	public function getTimeInSecondsSumWithDistance() {
		if ($this->hasProperty('s_sum_with_distance'))
			return $this->get('s_sum_with_distance');

		return $this->getTimeInSeconds();
	}


	/**
	 * Set elapsed time
	 * @param int $timeInSeconds duration
	 */
	public function setElapsedTime($timeInSeconds) { $this->set('elapsed_time', $timeInSeconds); }
	/**
	 * Get elapsed time
	 * @return int duration in seconds
	 */
	public function getElapsedTime() { return $this->get('elapsed_time'); }
	/**
	 * Has elapsed time?
	 * @return bool True if training has an elapsed time different from active time
	 */
	public function hasElapsedTime() { return $this->getElapsedTime() > 0 && $this->getElapsedTime() != $this->getTimeInSeconds(); }


	/**
	 * Get pace
	 * @return string
	 */
	public function getPace() { return $this->get('pace'); }


	/**
	 * Set elevation
	 * @param int $elevation elevation
	 */
	public function setElevation($elevation) { $this->set('elevation', $elevation); }
	/**
	 * Get elevation
	 * @return int
	 */
	public function getElevation() { return $this->get('elevation'); }


	/**
	 * Set calculated elevation
	 * @param int $elevation elevation
	 */
	public function setElevationCalculated($elevation) { $this->set('elevation_calculated', $elevation); }
	/**
	 * Get calculated elevation
	 * @return int
	 */
	public function getElevationCalculated() { return $this->get('elevation_calculated'); }

	/**
	 * Set calories
	 * @param int $kcal kcal
	 */
	public function setCalories($kcal) { $this->set('kcal', $kcal); }
	/**
	 * Add calories
	 * @param int $kcal
	 */
	public function addCalories($kcal) { $this->set('kcal', $this->getCalories() + $kcal); }
	/**
	 * Get calories
	 * @return int kcal
	 */
	public function getCalories() { return $this->get('kcal'); }


	/**
	 * Set average pulse
	 * @param int $bpm average heartrate in bpm
	 */
	public function setPulseAvg($bpm) { $this->set('pulse_avg', $bpm); }
	/**
	 * Get average pulse
	 * @return int average heartrate in bpm
	 */
	public function getPulseAvg() { return $this->get('pulse_avg'); }


	/**
	 * Set maximal pulse
	 * @param int $bpm maximal heartrate in bpm
	 */
	public function setPulseMax($bpm) { $this->set('pulse_max', $bpm); }
	/**
	 * Get maximal pulse
	 * @return int maximal heartrate in bpm
	 */
	public function getPulseMax() { return $this->get('pulse_max'); }


	public function setFitVO2maxEstimate($vo2max) { $this->set('fit_vo2max_estimate', $vo2max); }
	public function getFitVO2maxEstimate() { return $this->get('fit_vo2max_estimate'); }

	public function setFitRecoveryTime($minutes) { $this->set('fit_recovery_time', $minutes); }
	public function getFitRecoveryTime() { return $this->get('fit_recovery_time'); }

	public function setFitHRVscore($score) { $this->set('fit_hrv_analysis', $score); }
	public function getFitHRVscore() { return $this->get('fit_hrv_analysis'); }

	public function setFitTrainingEffect($effect) { $this->set('fit_training_effect', $effect); }
	public function getFitTrainingEffect() { return $this->get('fit_training_effect'); }

	public function setFitPerformanceCondition($value) { $this->set('fit_performance_condition', $value); }
	public function getFitPerformanceCondition() { return $this->get('fit_performance_condition'); }


	/**
	 * Set cadence
	 * @param int $cadence cadence in rpm
	 */
	public function setCadence($cadence) { $this->set('cadence', $cadence); }
	/**
	 * Get cadence
	 * @return int cadence in rpm
	 */
	public function getCadence() { return $this->get('cadence'); }


	/**
	 * Set power
	 * @param int $power power
	 */
	public function setPower($power) { $this->set('power', $power); }
	/**
	 * Get power
	 * @return int power value
	 */
	public function getPower() { return $this->get('power'); }


	/**
	 * Set ground contact time
	 * @param int $time ground contact time [ms]
	 */
	public function setGroundContactTime($time) { $this->set('groundcontact', $time); }
	/**
	 * Get ground contact time
	 * @return int ground contact time value [ms]
	 */
	public function getGroundContactTime() { return $this->get('groundcontact'); }


	/**
	 * Set vertical oscillation
	 * @param int $oscillation vertical oscillation [cm]
	 */
	public function setVerticalOscillation($oscillation) { $this->set('vertical_oscillation', $oscillation); }
	/**
	 * Get vertical oscillation
	 * @return int vertical oscillation [cm]
	 */
	public function getVerticalOscillation() { return $this->get('vertical_oscillation'); }


	/**
	 * Set vertical ratio
	 * @param int $verticalRatio vertical ratio [%]
	 */
	public function setVerticalRatio($verticalRatio) { $this->set('vertical_ratio', $verticalRatio); }
	/**
	 * Get vertical ratio
	 * @return int vertical ratio [%]
	 */
	public function getVerticalRatio() { return $this->get('vertical_ratio'); }

	/**
	 * Set ground contact time balance
	 * @param int $groundContactBalance ground contact time balance [%]
	 */
	public function setGroundContactBalance($groundContactBalance) { $this->set('groundcontact_balance', $groundContactBalance); }
	/**
	 * Get ground contact time balance
	 * @return int ground contact time balance [%]
	 */
	public function getGroundContactBalance() { return $this->get('groundcontact_balance'); }

	/**
	 * Set weatherid
	 * @param mixed $id weatherid
	 */
	public function setWeatherid($id) { $this->set('weatherid', $id); }


	/**
	 * Set temperature
	 * @param mixed $temp temperature in degree celsius, can be null
	 */
	public function setTemperature($temp) { $this->set('temperature', $temp); }


	/**
	 * Get route
	 * @return string route
	 */
	public function getRoute() { return $this->get('route'); }
	/**
	 * Set route
	 * @param string $route
	 */
	public function setRoute($route) { $this->set('route', $route); }


	/**
	 * Remove single splits
	 */
	public function removeSingleSplits() { $this->Splits()->removeSingleSplits(); }

	/**
	 * Set splits from splits object
	 *
	 * To add new splits, use $this->Splits()->addSplit()
	 */
	public function setSplitsFromObject() { $this->set('splits', $this->Splits()->asString()); }


	/**
	 * Set title
	 * @param string $title title
	 */
	public function setTitle($title) { $this->set('title', $title); }
	/**
	 * Get title
	 * @return string title
	 */
	public function getTitle() { return $this->get('title'); }
	/**
	 * Has title?
	 * @return bool
	 */
	public function hasTitle() { return strlen($this->get('title')) > 0; }

	/**
	 * Set total strokes
	 * @param string $totalStrokes total strokes
	 */
	public function setTotalStrokes($totalStrokes) { $this->set('total_strokes', $totalStrokes); }
	/**
	 * Get total strokes
	 * @return string total strokes
	 */
	public function getTotalStrokes() { return $this->get('total_strokes'); }

	/**
	 * Set swolf
	 * @param string $swolf swolf
	 */
	public function setSwolf($swolf) { $this->set('swolf', $swolf); }
	/**
	 * Get swolf
	 * @return string swolf
	 */
	public function getSwolf() { return $this->get('swolf'); }

	/**
	 * Set pool length
	 * @param string $poollength pool length
	 */
	public function setPoolLength($poollength) { $this->set('pool_length', $poollength); }
	/**
	 * Get pool length
	 * @return string pool length
	 */
	public function getPoolLength() { return $this->get('pool_length'); }

	/**
	 * Get partner
	 * @return string partner
	 */
	public function getPartner() { return $this->get('partner'); }

	/**
	 * Set notes
	 * @param string $notes string
	 */
	public function setNotes($notes) { $this->set('notes', $notes); }
	/**
	 * Get notes
	 * @return string notes
	 */
	public function getNotes() { return $this->get('notes'); }

	/**
	 * Set array for time
	 * @param array $array array with timepoints
	 */
	public function setArrayTime($array) { $this->setArrayFor('arr_time', $array); }

	/**
	 * Get array for time
	 * @return array array with timepoints
	 */
	public function getArrayTime() { return $this->getArrayFor('arr_time'); }

	/**
	 * Get last time point
	 * @return int
	 */
	public function getArrayTimeLastPoint() { return $this->getLastArrayPoint('arr_time'); }

	/**
	 * Has array time?
	 * @return bool
	 */
	public function hasArrayTime() { return strlen($this->get('arr_time')) > 0; }

	/**
	 * Set array for latitude
	 * @param array $array
	 */
	public function setArrayLatitude($array) { $this->setArrayFor('arr_lat', $array); }

	/**
	 * Get array for latitude
	 * @return array
	 */
	public function getArrayLatitude() { return $this->getArrayFor('arr_lat'); }

	/**
	 * Has array for latitude?
	 * @return bool
	 */
	public function hasArrayLatitude() { return strlen($this->get('arr_lat')) > 0; }

	/**
	 * Set array for geohashes
	 * @param array $array
	 */
	public function setArrayGeohashes($array) { $this->setArrayFor('arr_geohashes', $array); }

	/**
	 * Get array for geohashes
	 * @return array
	 */
	public function getArrayGeohashes() { return $this->getArrayFor('arr_geohashes'); }

	/**
	 * Has array for geohashes?
	 * @return bool
	 */
	public function hasArrayGeohashes() { return strlen($this->get('arr_geohashes')) > 0; }

	/**
	 * Set array for longitude
	 * @param array $array
	 */
	public function setArrayLongitude($array) { $this->setArrayFor('arr_lon', $array); }
	/**
	 * Get array for longitude
	 * @return array
	 */
	public function getArrayLongitude() { return $this->getArrayFor('arr_lon'); }
	/**
	 * Has array for longitude?
	 * @return bool
	 */
	public function hasArrayLongitude() { return strlen($this->get('arr_lon')) > 0; }


	/**
	 * Set array for swim stroke
	 * @param array $data
	 */
	public function setArrayStroke(array $data) { $this->setArrayFor('stroke', $data); }

	/**
	 * Get array for swim stroke
	 * @return array
	 */
	public function getArrayStroke() { return $this->getArrayFor('stroke'); }

	/**
	 * Has array for swim stroke?
	 * @return bool
	 */
	public function hasArrayStroke() { return strlen($this->get('stroke')) > 0; }


	/**
	 * Set array for swim stroke type
	 * @param array $data
	 */
	public function setArrayStrokeType(array $data) { $this->setArrayFor('stroketype', $data); }

        /**
	 * Get array for swim stroke type
	 * @return array
	 */
	public function getArrayStrokeType() { return $this->getArrayFor('stroketype'); }

	/**
	 * Has array for swim stroke type?
	 * @return bool
	 */
	public function hasArrayStrokeType() { return strlen($this->get('stroketype')) > 0; }

	/**
	 * Has position data?
	 * @return bool True if latitude and longitude arrays are set.
	 */
	public function hasPositionData() {
		return $this->hasArrayLatitude() && $this->hasArrayLongitude();
	}

	/**
	 * Set array for altitude
	 * @param array $array
	 */
	public function setArrayAltitude($array) { $this->setArrayFor('arr_alt', $array); }
	/**
	 * Get array for altitude
	 * @return array
	 */
	public function getArrayAltitude() { return $this->getArrayFor('arr_alt'); }
	/**
	 * Has array for altitude?
	 * @return bool
	 */
	public function hasArrayAltitude() { return strlen($this->get('arr_alt')) > 0; }


	/**
	 * Get array for original altitude
	 * @return array
	 */
	public function getArrayAltitudeOriginal() { return $this->getArrayFor('arr_alt_original'); }
	/**
	 * Has array for original altitude?
	 * @return bool
	 */
	public function hasArrayAltitudeOriginal() { return strlen($this->get('arr_alt_original')) > 0; }


	/**
	 * Set array for distance
	 * @param array $array
	 */
	public function setArrayDistance($array) { $this->setArrayFor('arr_dist', $array); }
	/**
	 * Get array for distance
	 * @return array
	 */
	public function getArrayDistance() { return $this->getArrayFor('arr_dist'); }
	/**
	 * Get last distance point
	 * @return float
	 */
	public function getArrayDistanceLastPoint() { return $this->getLastArrayPoint('arr_dist'); }
	/**
	 * Has array for distance?
	 * @return bool
	 */
	public function hasArrayDistance() { return strlen($this->get('arr_dist')) > 0; }


	/**
	 * Set array for heartrate
	 * @param array $array
	 */
	public function setArrayHeartrate($array) { $this->setArrayFor('arr_heart', $array); }
	/**
	 * Get array for heartrate
	 * @return array
	 */
	public function getArrayHeartrate() { return $this->getArrayFor('arr_heart'); }
	/**
	 * Has array for heartrate?
	 * @return bool
	 */
	public function hasArrayHeartrate() { return strlen($this->get('arr_heart')) > 0; }


	/**
	 * Set array for pace
	 * @param array $array
	 */
	public function setArrayPace($array) { $this->setArrayFor('arr_pace', $array); }
	/**
	 * Get array for pace
	 * @return array
	 */
	public function getArrayPace() { return $this->getArrayFor('arr_pace'); }
	/**
	 * Has array for pace?
	 * @return bool
	 */
	public function hasArrayPace() { return strlen($this->get('arr_pace')) > 0; }


	/**
	 * Set array for cadence
	 * @param array $array cadence values in rpm
	 */
	public function setArrayCadence($array) { $this->setArrayFor('arr_cadence', $array); }
	/**
	 * Get array for cadence
	 * @return array cadence values in rpm
	 */
	public function getArrayCadence() { return $this->getArrayFor('arr_cadence'); }
	/**
	 * Get manipulated array for cadence
	 * @return array cadence values manipulated for e.g. spm
	 */
	public function getArrayCadenceManipulated() { return $this->Cadence()->manipulateArray( $this->getArrayFor('arr_cadence') ); }
	/**
	 * Has array for cadence?
	 * @return bool
	 */
	public function hasArrayCadence() { return strlen($this->get('arr_cadence')) > 0; }


	/**
	 * Set array for power
	 * @param array $array
	 */
	public function setArrayPower($array) { $this->setArrayFor('arr_power', $array); }
	/**
	 * Get array for power
	 * @return array
	 */
	public function getArrayPower() { return $this->getArrayFor('arr_power'); }
	/**
	 * Has array for power?
	 * @return bool
	 */
	public function hasArrayPower() { return strlen($this->get('arr_power')) > 0; }


	/**
	 * Set array for temperature
	 * @param array $array
	 */
	public function setArrayTemperature($array) { $this->setArrayFor('arr_temperature', $array); }
	/**
	 * Get array for temperature
	 * @return array
	 */
	public function getArrayTemperature() { return $this->getArrayFor('arr_temperature'); }
	/**
	 * Has array for temperature?
	 * @return bool
	 */
	public function hasArrayTemperature() { return strlen($this->get('arr_temperature')) > 0; }


	/**
	 * Set array for ground contact
	 * @param array $data
	 */
	public function setArrayGroundContact(array $data) { $this->setArrayFor('arr_groundcontact', $data); }
	/**
	 * Get array for ground contact
	 * @return array
	 */
	public function getArrayGroundContact() { return $this->getArrayFor('arr_groundcontact'); }
	/**
	 * Has array for ground contact?
	 * @return bool
	 */
	public function hasArrayGroundContact() { return strlen($this->get('arr_groundcontact')) > 0; }


	/**
	 * Set array for vertical oscillation
	 * @param array $data
	 */
	public function setArrayVerticalOscillation(array $data) { $this->setArrayFor('arr_vertical_oscillation', $data); }
	/**
	 * Get array for vertical oscillation
	 * @return array
	 */
	public function getArrayVerticalOscillation() { return $this->getArrayFor('arr_vertical_oscillation'); }
	/**
	 * Has array for vertical oscillation?
	 * @return bool
	 */
	public function hasArrayVerticalOscillation() { return strlen($this->get('arr_vertical_oscillation')) > 0; }


	/**
	 * Set array for ground contact time balance
	 * @param array $data
	 */
	public function setArrayGroundContactBalance(array $data) { $this->setArrayFor('arr_groundcontact_balance', $data); }
	/**
	 * Get array for ground contact time balance
	 * @return array
	 */
	public function getArrayGroundContactBalance() { return $this->getArrayFor('arr_groundcontact_balance'); }
	/**
	 * Has array for ground contact time balance?
	 * @return bool
	 */
	public function hasArrayGroundContactBalance() { return strlen($this->get('arr_groundcontact_balance')) > 0; }


	/**
	 * Set array for heart rate variability
	 * @param array $data
	 */
	public function setArrayHRV(array $data) { $this->setArrayFor('hrv', $data); }
	/**
	 * Get array for vertical oscillation
	 * @return array
	 */
	public function getArrayHRV() { return $this->getArrayFor('hrv'); }
	/**
	 * Has array for vertical oscillation?
	 * @return bool
	 */
	public function hasArrayHRV() { return ($this->get('hrv') != ''); }

    /**
     * Set
     * @param int $smo2_0  absolute measure of oxygen concentration in hemoglobin [%]
     */
    public function setArraySmo2_0(array $smo2_0) { $this->setArrayFor('arr_smo2_0', $smo2_0); }
    /**
     * Get  absolute measure of oxygen concentration
     * @return int  []
     */
    public function getArraySmo2_0() { return $this->getArrayFor('arr_smo2_0'); }
    /**
     * Has array for absolute measure of oxygen concentration?
     * @return bool
     */
    public function hasArraySmo2_0() { return ($this->get('arr_smo2_0') != ''); }

    /**
     * Set
     * @param int $smo2_1  absolute measure of oxygen concentration in hemoglobin [%]
     */
    public function setArraySmo2_1(array $smo2_1) { $this->setArrayFor('arr_smo2_1', $smo2_1); }
    /**
     * Get  absolute measure of oxygen concentration
     * @return int  []
     */
    public function getArraySmo2_1() { return $this->getArrayFor('arr_smo2_1'); }
    /**
     * Has array for absolute measure of oxygen concentration?
     * @return bool
     */
    public function hasArraySmo2_1() { return ($this->get('arr_smo2_1') != ''); }

    /**
     * Set
     * @param int $thb_0
     */
    public function setArrayThb_0(array $thb_0) { $this->setArrayFor('arr_thb_0', $thb_0); }
    /**
     * Get Thb
     * @return int  []
     */
    public function getArrayThb_0() { return $this->getArrayFor('arr_thb_0'); }
    /**
     * Has array for thb_0
     * @return bool
     */
    public function hasArrayThb_0() { return ($this->get('arr_thb_0') != ''); }

    /**
     * Set
     * @param int $thb_1
     */
    public function setArrayThb_1(array $thb_1) { $this->setArrayFor('arr_thb_1', $thb_1); }
    /**
     * Get Thb
     * @return int  []
     */
    public function getArrayThb_1() { return $this->getArrayFor('arr_thb_1'); }
    /**
     * Has array for thb_1
     * @return bool
     */
    public function hasArrayThb_1() { return ($this->get('arr_thb_1') != ''); }

	/**
	 * Set creator
	 * @param string $creator
	 */
	public function setCreator($creator) { $this->set('creator', $creator); }
	/**
	 * Get creator
	 * @return string
	 */
	public function getCreator() { return $this->get('creator'); }


	/**
	 * Set creator details
	 * @param string $details
	 */
	public function setCreatorDetails($details) { $this->set('creator_details', $details); }
	/**
	 * Get creator details
	 * @return string
	 */
	public function getCreatorDetails() { return $this->get('creator_details'); }


	/**
	 * Set activity id
	 * @param string $id activity id from garmin device
	 */
	public function setActivityId($id) { $this->set('activity_id', $id); }
	/**
	 * Get activity id
	 * @return string activity id from garmin device
	 */
	public function getActivityId() { return $this->get('activity_id'); }
	/**
	 * Has activity id?
	 * @return bool True if training has a (positive) distance.
	 */
	public function hasActivityId() { return $this->getActivityId() !== NULL; }


	/**
	 * Was elevation corrected?
	 * @return bool
	 */
	public function elevationWasCorrected() { return $this->get('elevation_corrected') == 1; }
}
