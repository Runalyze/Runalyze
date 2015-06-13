<?php
/**
 * This file contains class::TrainingObject
 * @package Runalyze\DataObjects\Training
 */

use Runalyze\Configuration;

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
	 * Type
	 * @var \Type
	 */
	private $Type = null;

	/**
	 * Shoe
	 * @var \Shoe
	 */
	private $Shoe = null;

	/**
	 * Clothes
	 * @var \Clothes
	 */
	private $Clothes = null;

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
	 * @var \Cadence
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
		$this->set('time', isset($_GET['date']) ? strtotime($_GET['date']) : mktime(0,0,0));
		$this->set('is_public', Configuration::Privacy()->publishActivity() ? '1' : '0');
		$this->forceToSet('s_sum_with_distance', 0);
	}

	/**
	 * Set weather forecast
	 */
	public function setWeatherForecast() {
		if ($this->trainingIsTooOldToFetchWeatherData() || !Configuration::ActivityForm()->loadWeather())
			return;

		$Strategy = new \Runalyze\Data\Weather\Openweathermap();
		$Location = new \Runalyze\Data\Weather\Location();
		$Location->setTimestamp($this->getTimestamp());
		$Location->setLocationName(Configuration::ActivityForm()->weatherLocation());

		if ($this->hasPositionData()) {
			$Location->setPosition( $this->getFirstArrayPoint('arr_lat'), $this->getFirstArrayPoint('arr_lon') );
		}

		$Forecast = new \Runalyze\Data\Weather\Forecast($Strategy, $Location);
		$Weather = $Forecast->object();
		$Weather->temperature()->toCelsius();

		$this->set('weatherid', $Weather->condition()->id());
		$this->set('temperature', $Weather->temperature()->value());
	}

	/**
	 * Check: Is this training too old for weather forecast?
	 * @return boolean
	 */
	private function trainingIsTooOldToFetchWeatherData() {
		return Time::diffInDays($this->getTimestamp()) > 30;
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
		$Route = $this->newRouteObject();
		$Trackdata = $this->newTrackdataObject();

		if ($Route->name() != '' || $Route->hasPositionData() || $Route->hasElevations()) {
			$InserterRoute = new Runalyze\Model\Route\Inserter(DB::getInstance(), $Route);
			$InserterRoute->setAccountID( SessionAccountHandler::getId() );
			$InserterRoute->insert();

			$this->forceToSet('routeid', $InserterRoute->insertedID());

			if ($this->getElevation() == 0) {
				$this->setElevation($Route->elevation());
			}
		}

		$Activity = $this->newActivityObject();

		$InserterActivity = new Runalyze\Model\Activity\Inserter(DB::getInstance(), $Activity);
		$InserterActivity->setAccountID( SessionAccountHandler::getId() );
		$InserterActivity->setRoute($Route);
		$InserterActivity->setTrackdata($Trackdata);
		$InserterActivity->insert();

		$this->id = $InserterActivity->insertedID();

		if ($this->hasArrayTime() || $this->hasArrayDistance() || $this->hasArrayPace() || $this->hasArrayHeartrate()) {
			$Trackdata->set(Runalyze\Model\Trackdata\Object::ACTIVITYID, $this->id());
			$InserterTrack = new Runalyze\Model\Trackdata\Inserter(DB::getInstance(), $Trackdata);
			$InserterTrack->setAccountID( SessionAccountHandler::getId() );
			$InserterTrack->insert();
		}
	}

	/**
	 * @return \Runalyze\Model\Activity\Object
	 */
	protected function newActivityObject() {
		return new Runalyze\Model\Activity\Object($this->data);
	}

	/**
	 * @return \Runalyze\Model\Route\Object
	 */
	protected function newRouteObject() {
		return new Runalyze\Model\Route\Object(array(
			Runalyze\Model\Route\Object::NAME => $this->get('route'),
			Runalyze\Model\Route\Object::CITIES => $this->get('route'),
			Runalyze\Model\Route\Object::DISTANCE => $this->get('distance'),
			Runalyze\Model\Route\Object::LATITUDES => $this->get('arr_lat'),
			Runalyze\Model\Route\Object::LONGITUDES => $this->get('arr_lon'),
			Runalyze\Model\Route\Object::ELEVATIONS_ORIGINAL => $this->get('arr_alt')
		));
	}

	/**
	 * @return \Runalyze\Model\Trackdata\Object
	 */
	protected function newTrackdataObject() {
		return new Runalyze\Model\Trackdata\Object(array(
			Runalyze\Model\Trackdata\Object::TIME => $this->get('arr_time'),
			Runalyze\Model\Trackdata\Object::DISTANCE => $this->get('arr_dist'),
			Runalyze\Model\Trackdata\Object::PACE => $this->get('arr_pace'),
			Runalyze\Model\Trackdata\Object::HEARTRATE => $this->get('arr_heart'),
			Runalyze\Model\Trackdata\Object::CADENCE => $this->get('arr_cadence'),
			Runalyze\Model\Trackdata\Object::POWER => $this->get('arr_power'),
			Runalyze\Model\Trackdata\Object::TEMPERATURE => $this->get('arr_temperature'),
			Runalyze\Model\Trackdata\Object::GROUNDCONTACT => $this->get('arr_groundcontact'),
			Runalyze\Model\Trackdata\Object::VERTICAL_OSCILLATION => $this->get('arr_vertical_oscillation'),
			Runalyze\Model\Trackdata\Object::PAUSES => $this->get('pauses')
		));
	}

	/**
	 * Update object in database
	 */
	public function update() {
		$OldData = isset($_POST['old-data'])
				? unserialize(base64_decode($_POST['old-data']))
				: array();

		if (!is_array($OldData)) {
			$OldData = array();
		}

		$UpdaterActivity = new \Runalyze\Model\Activity\Updater(DB::getInstance(),
			$this->newActivityObject(),
			new \Runalyze\Model\Activity\Object($OldData)
		);

		if (isset($OldData['routeid'])) {
			$UpdaterActivity->setRoute(\Runalyze\Context::Factory()->route($OldData['routeid']));
		}

		$UpdaterActivity->setTrackdata(\Runalyze\Context::Factory()->trackdata($this->id()));
		$UpdaterActivity->setAccountID( SessionAccountHandler::getId() );
		$UpdaterActivity->update();

		if (isset($OldData['routeid']) && isset($OldData['route'])) {
			$UpdaterRoute = new \Runalyze\Model\Route\Updater(DB::getInstance(),
				new Runalyze\Model\Route\Object(array(
					'id' => $OldData['routeid'],
					Runalyze\Model\Route\Object::NAME => $this->get('route'),
					Runalyze\Model\Route\Object::CITIES => $this->get('route')
				)),
				new Runalyze\Model\Route\Object(array(
					'id' => $OldData['routeid'],
					Runalyze\Model\Route\Object::NAME => $OldData['route'],
					Runalyze\Model\Route\Object::CITIES => $OldData['route']
				))
			);
			$UpdaterRoute->setAccountID( SessionAccountHandler::getId() );
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
	 * Type object
	 * @return \Type
	 */
	public function Type() {
		if (is_null($this->Type)) {
			if (!$this->hasProperty('typeid'))
				$this->Type = new Type(0);
			else
				$this->Type = new Type($this->get('typeid'));
		}

		return $this->Type;
	}

	/**
	 * Shoe object
	 * @return \Shoe
	 */
	public function Shoe() {
		if (is_null($this->Shoe))
			$this->Shoe = new Shoe($this->get('shoeid'));

		return $this->Shoe;
	}

	/**
	 * Clothes object
	 * @return \Clothes
	 */
	public function Clothes() {
		if (is_null($this->Clothes))
			$this->Clothes = new Clothes($this->get('clothes'));

		return $this->Clothes;
	}

	/**
	 * Weather object
	 * @return \Runalyze\Data\Weather
	 */
	public function Weather() {
		if (is_null($this->Weather)) {
			$id   = ($this->hasProperty('weatherid')) ? $this->get('weatherid') : \Runalyze\Data\Weather\Condition::UNKNOWN;
			$temp = ($this->hasProperty('temperature')) ? $this->get('temperature') : null;

			$this->Weather = new \Runalyze\Data\Weather(
				new \Runalyze\Data\Weather\Temperature($temp),
				new \Runalyze\Data\Weather\Condition($id)
			);
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
	 * @return \Cadence
	 */
	public function Cadence() {
		if (is_null($this->Cadence)) {
			if ($this->Sport()->isRunning())
				$this->Cadence = new CadenceRunning($this->get('cadence'));
			else
				$this->Cadence = new Cadence($this->get('cadence'));
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
	 * Set shoeid
	 * @param int $id shoeid
	 */
	public function setShoeid($id) { $this->set('shoeid', $id); }


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


	/**
	 * Get uncorrected VDOT
	 * 
	 * This value is calculated by heartrate and pace without any correction.
	 * @return double uncorrected vdot
	 */
	public function getVdotUncorrected() { return $this->get('vdot'); }
	/**
	 * Get corrected VDOT
	 * 
	 * This value is calculated by heartrate and pace and corrected by
	 * the user defined/calculated correction factor.
	 * @return double corrected vdot
	 */
	public function getVdotCorrected() { return round(Configuration::Data()->vdotFactor()*$this->getVdotUncorrected(), 2); }
	/**
	 * Get VDOT by time
	 * 
	 * This value is calculated by distance and time without any influence by heartrate.
	 * @return double vdot by time
	 */
	public function getVdotByTime() { return $this->get('vdot_by_time'); }
	/**
	 * Get VDOT with elevation
	 * @return double vdot with elevation influence
	 */
	public function getVdotWithElevation() { return $this->get('vdot_with_elevation'); }
	/**
	 * Get VDOT with elevation corrected
	 * @return double vdot with elevation influence
	 */
	public function getVdotWithElevationCorrected() { return round(Configuration::Data()->vdotFactor()*$this->getVdotWithElevation(), 2); }
	/**
	 * Get VDOT with elevation
	 * @return double vdot with elevation influence
	 */
	public function getCurrentlyUsedVdot() { return (Configuration::Vdot()->useElevationCorrection() && $this->getVdotWithElevation() > 0 ? $this->getVdotWithElevationCorrected() : $this->getVdotCorrected()); }


	/**
	 * Used for vdot?
	 * 
	 * A user can decide if we wants a training to be used for vdot-shape-calculation.
	 * @return bool True if user wants this training to influence vdot-shape.
	 */
	public function usedForVdot() { return $this->get('use_vdot') == 1; }


	/**
	 * Get JD intensity
	 * @return int jd intensity
	 */
	public function getJDintensity() { return $this->get('jd_intensity'); }


	/**
	 * Get trimp
	 * @return int trimp value
	 */
	public function getTrimp() { return $this->get('trimp'); }


	/**
	 * Set cadence
	 * @param int $cadence cadence in rpm
	 */
	public function setCadence($cadence) { return $this->set('cadence', $cadence); }
	/**
	 * Get cadence
	 * @return int cadence in rpm
	 */
	public function getCadence() { return $this->get('cadence'); }


	/**
	 * Set power
	 * @param int $power power
	 */
	public function setPower($power) { return $this->set('power', $power); }
	/**
	 * Get power
	 * @return int power value
	 */
	public function getPower() { return $this->get('power'); }


	/**
	 * Set ground contact time
	 * @param int $time ground contact time [ms]
	 */
	public function setGroundContactTime($time) { return $this->set('groundcontact', $time); }
	/**
	 * Get ground contact time
	 * @return int ground contact time value [ms]
	 */
	public function getGroundContactTime() { return $this->get('groundcontact'); }


	/**
	 * Set vertical oscillation
	 * @param int $oscillation vertical oscillation [cm]
	 */
	public function setVerticalOscillation($oscillation) { return $this->set('vertical_oscillation', $oscillation); }
	/**
	 * Get vertical oscillation
	 * @return int vertical oscillation [cm]
	 */
	public function getVerticalOscillation() { return $this->get('vertical_oscillation'); }


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
	 * Set comment
	 * @param string $comment comment
	 */
	public function setComment($comment) { $this->set('comment', $comment); }
	/**
	 * Get comment
	 * @return string comment
	 */
	public function getComment() { return $this->get('comment'); }
	/**
	 * Has comment?
	 * @return bool
	 */
	public function hasComment() { return strlen($this->get('comment')) > 0; }


	/**
	 * Get partner
	 * @return string partner
	 */
	public function getPartner() { return $this->get('partner'); }


	/**
	 * Was with running abc?
	 * @return bool True if this training was with 'running abc'
	 */
	public function wasWithABC() { return $this->get('abc') == 1; }


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
	 * Has position data?
	 * @return bool True if latitude and longitude arrays are set.
	 */
	public function hasPositionData() { 
		if (Request::isOnSharedPage() && $this->hidesMap())
			return false;

		return $this->hasArrayLatitude() && $this->hasArrayLongitude();
	}

	/**
	 * Hides map?
	 * @return boolean
	 */
	public function hidesMap() {
		$RoutePrivacy = Configuration::Privacy()->RoutePrivacy();

		if ($RoutePrivacy->showRace()) {
			return (!$this->Type()->isCompetition());
		} elseif ($RoutePrivacy->showAlways()) {
			return false;
		}

		return true;
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
	 * Was elevation corrected?
	 * @return bool
	 */
	public function elevationWasCorrected() { return $this->get('elevation_corrected') == 1; }

	/**
	 * Was this training a competition?
	 * @param int $id id
	 * @return boolean 
	 */
	static public function idIsCompetition($id) {
		return (DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'training` WHERE `id`='.(int)$id.' AND `typeid`="'.Configuration::General()->competitionType().'" LIMIT 1')->fetchColumn() > 0);
	}
}