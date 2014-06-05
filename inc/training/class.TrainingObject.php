<?php
/**
 * This file contains class::TrainingObject
 * @package Runalyze\DataObjects\Training
 */
/**
 * DataObject for trainings
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training
 */
class TrainingObject extends DataObject {
	/**
	 * Data view
	 * @var \TrainingDataView
	 */
	private $DataView = null;

	/**
	 * Linker
	 * @var \TrainingLinker
	 */
	private $Linker = null;

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
	 * Object for given GPS-data
	 * @var \GpsData
	 */
	private $GpsData = null;

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
	 * @var \Weather
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
	 * Fill default object with standard settings and weather forecast if needed
	 */
	protected function fillDefaultObject() {
		$this->set('time', isset($_GET['date']) ? strtotime($_GET['date']) : mktime(0,0,0));
		$this->set('is_public', CONF_TRAINING_MAKE_PUBLIC ? '1' : '0');
		$this->forceToSet('s_sum_with_distance', 0);
	}

	/**
	 * Set weather forecast
	 */
	public function setWeatherForecast() {
		if ($this->trainingIsTooOldToFetchWeatherData() || !CONF_TRAINING_LOAD_WEATHER)
			return;

		$Weather = new WeatherForecast();
		$this->set('weatherid', $Weather->id());
		$this->set('temperature', $Weather->temperature());
	}

	/**
	 * Check: Is this training too old for weather forecast?
	 * @return boolean
	 */
	private function trainingIsTooOldToFetchWeatherData() {
		return Time::diffInDays($this->getTimestamp()) > 2;
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
	}

	/**
	 * Tasks to perform before insert
	 */
	protected function tasksBeforeInsert() {
		$this->set('created', time());
		$this->set('arr_alt_original', $this->get('arr_alt'));
		$this->setPaceFromData();
		$this->calculateCaloriesIfEmpty();
		$this->removeWeatherIfInside();
		$this->forceToRemove('s_sum_with_distance');
	}

	/**
	 * Insert to database
	 */
	protected function insertToDatabase() {
		if ($this->getTimeInSeconds() == 0)
			Error::getInstance()->addError( __('The training has not been created. A time has to be set.') );
		else
			parent::insertToDatabase();
	}

	/**
	 * Tasks to perform after insert
	 */
	protected function tasksAfterInsert() {
		$this->updateTrimp();
		$this->updateElevation();

		if ($this->get('sportid') == CONF_RUNNINGSPORT) {
			$this->updateVdot();
			$this->updateShoeForInsert();

			JD::recalculateVDOTform();
			BasicEndurance::recalculateValue();
		}

		Helper::recalculateStartTime();

		if ($this->Sport()->usesPower() && CONF_COMPUTE_POWER)
			$this->calculatePower();
	}

	/**
	 * Tasks to perform before update
	 */
	protected function tasksBeforeUpdate() {
		$this->set('edited', time());
		$this->setPaceFromData();
	}

	/**
	 * Tasks to perform after update
	 */
	protected function tasksAfterUpdate() {
		$this->updateTrimp();

		if ($this->get('sportid') == CONF_RUNNINGSPORT) {
			$this->updateVdot();
			$this->updateShoeForUpdate();

			JD::recalculateVDOTform();
			BasicEndurance::recalculateValue();
		}

		Helper::recalculateStartTime();
	}

	/**
	 * Set pace from post
	 */
	private function setPaceFromData() {
		$this->set('pace', SportSpeed::minPerKm($this->getDistance(), $this->getTimeInSeconds()));
	}

	/**
	 * Calculate calories if empty
	 */
	private function calculateCaloriesIfEmpty() {
		if ($this->getCalories() == 0)
			$this->setCalories( round(SportFactory::kcalPerHourFor($this->get('sportid'))*$this->getTimeInSeconds()/3600) );
	}

	/**
	 * Remove weather before insert if
	 */
	private function removeWeatherIfInside() {
		if (!$this->Sport()->isOutside()) {
			$this->setTemperature(null);
			$this->setWeatherid(Weather::$UNKNOWN_ID);
		}
	}

	/**
	 * Update trimp
	 */
	private function updateTrimp() {
		$this->updateValue('trimp', Trimp::forTraining($this->getArray()));

		Trimp::checkForMaxValuesAt($this->getTimestamp());
	}

	/**
	 * Update vdot
	 */
	private function updateVdot() {
		$this->updateValue('vdot_by_time', JD::Competition2VDOT($this->get('distance'), $this->get('s')));
		$this->updateValue('vdot', JD::Training2VDOT($this->id(), $this->getArray()));
		$this->updateValue('jd_intensity', JD::Training2points($this->id(), $this->getArray()));
		$this->updateVdotWithElevation();

		if ($this->Type()->isCompetition())
			JD::recalculateVDOTcorrector();
	}

	/**
	 * Update vdot with elevation
	 * @param int $up [optional]
	 * @param int $down [optional]
	 */
	private function updateVdotWithElevation($up = false, $down = false) {
		$this->updateValue('vdot_with_elevation', JD::Training2VDOTwithElevation($this->id(), $this->getArray(), $up, $down));
	}

	/**
	 * Update shoe-data after insert
	 */
	private function updateShoeForInsert() {
		if ($this->get('shoeid') > 0)
			DB::getInstance()->exec('UPDATE `'.PREFIX.'shoe` SET `km`=`km`+'.$this->get('distance').', `time`=`time`+'.$this->get('s').' WHERE `id`='.$this->get('shoeid').' LIMIT 1');
	}

	/**
	 * Update shoe values 
	 */
	private function updateShoeForUpdate() {
		if ((isset($_POST['shoeid_old']) || $this->get('shoeid') > 0)
				&& isset($_POST['s_old'])
				&& isset($_POST['dist_old'])) {

			if (isset($_POST['shoeid_old']))
				DB::getInstance()->exec('UPDATE `'.PREFIX.'shoe` SET `km`=`km`-"'.$_POST['dist_old'].'", `time`=`time`-'.$_POST['s_old'].' WHERE `id`='.$_POST['shoeid_old'].' LIMIT 1');
			if ($this->get('shoeid') > 0)
				DB::getInstance()->exec('UPDATE `'.PREFIX.'shoe` SET `km`=`km`+"'.$this->get('distance').'", `time`=`time`+'.$this->get('s').' WHERE `id`='.$this->get('shoeid').' LIMIT 1');
		}
	}

	/**
	 * Do elevation correction
	 */
	private function updateElevation() {
		if ($this->hasArrayLatitude() && $this->hasArrayLongitude()) {
			if (CONF_TRAINING_DO_ELEVATION) {
				$this->doElevationCorrection();
			}

			$this->calculateElevation();

			if ($this->get('elevation') == 0) {
				$this->updateValue('elevation', $this->get('elevation_calculated'));
			}
		}
	}

	/**
	 * Calculate power
	 */
	private function calculatePower() {
		$GPS = new GpsData($this->getArray());
		$data = $GPS->calculatePower();

		$this->updateValue('arr_power', implode(self::$ARR_SEP, $data));
		$this->updateValue('power', $GPS->averagePower());
		$this->updateValue('gps_cache_object', '');
	}

	/**
	 * Try to correct elevation
	 */
	public function tryToCorrectElevation() {
		$this->doElevationCorrection();

		if ($this->elevationWasCorrected()) {
			$this->calculateElevation();

			if ($this->get('elevation') == 0) {
				$this->updateValue('elevation', $this->get('elevation_calculated'));
			}

			if ($this->Sport()->usesPower() && CONF_COMPUTE_POWER)
				$this->calculatePower();
		}
	}

	/**
	 * Do elevation correction
	 */
	private function doElevationCorrection() {
		$GPS  = new GpsData($this->getArray());
		$data = $GPS->getElevationCorrection();

		if (is_array($data)) {
			$this->updateValue('arr_alt', implode(self::$ARR_SEP, $data));
			$this->updateValue('elevation_corrected', 1);
			$this->updateValue('gps_cache_object', '');
		}
	}

	/**
	 * Calculate elevation
	 */
	public function calculateElevation() {
		$GPS = new GpsData($this->getArray());
		$this->updateValue('elevation_calculated', $GPS->calculateElevation());
	}

	/**
	 * Set calculated value as elevation
	 */
	public function setCalculatedValueAsElevation() {
		$GPS = new GpsData($this->getArray());
		$array = $GPS->calculateElevation(true);

		$this->updateValue('elevation', $array[0]);
		$this->updateVdotWithElevation($array[1], $array[2]);
	}


	/**************************************************************************
	 *
	 * The following methods serve simple get/set-methods for internal objects
	 *
	 *************************************************************************/

	/**
	 * Get RPE
	 * @return int 
	 */
	public function RPE() {
		if (!$this->Type()->isUnknown())
			return $this->Type()->RPE();

		return $this->Sport()->RPE();
	}

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
	 * TrainingDataView object
	 * @return TrainingDataView
	 */
	public function DataView() {
		if (is_null($this->DataView))
			$this->DataView = new TrainingDataView($this);

		return $this->DataView;
	}

	/**
	 * TrainingLinker object
	 * @return TrainingLinker
	 */
	public function Linker() {
		if (is_null($this->Linker))
			$this->Linker = new TrainingLinker($this);

		return $this->Linker;
	}

	/**
	 * GpsData object
	 * @return GpsData
	 */
	public function GpsData() {
		if (is_null($this->GpsData))
			$this->GpsData = new GpsData($this->getArray());

		return $this->GpsData;
	}

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
	 * @return \Weather
	 */
	public function Weather() {
		if (is_null($this->Weather)) {
			$id   = ($this->hasProperty('weatherid')) ? $this->get('weatherid') : 0;
			$temp = ($this->hasProperty('temperature')) ? $this->get('temperature') : null;
			$this->Weather = new Weather($id, $temp);
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
	 * Get elevation up
	 * @return int
	 */
	public function getElevationUp() {
		if ($this->getElevation() == 0)
			return '';

		$updown = $this->GpsData()->getElevationUpDownOfStep(true);

		return $updown[0];
	}
	/**
	 * Get elevation down
	 * @return int
	 */
	public function getElevationDown() {
		if ($this->getElevation() == 0)
			return '';

		$updown = $this->GpsData()->getElevationUpDownOfStep(true);

		return $updown[1];
	}


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
	public function getVdotCorrected() { return round(JD::correctVDOT($this->getVdotUncorrected()), 2); }
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
	public function getVdotWithElevationCorrected() { return round(JD::correctVDOT($this->getVdotWithElevation()), 2); }
	/**
	 * Get VDOT with elevation
	 * @return double vdot with elevation influence
	 */
	public function getCurrentlyUsedVdot() { return (CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION && $this->getVdotWithElevation() > 0 ? $this->getVdotWithElevationCorrected() : $this->getVdotCorrected()); }


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
		switch (CONF_TRAINING_MAP_PUBLIC_MODE) {
			case 'never':
				return true;
			case 'race':
				return (!$this->Type()->isCompetition());
			case 'race-longjog':
				return (!$this->Type()->isCompetition() && !$this->Type()->isLongJog());
			case 'always':
			default:
				return false;
		}
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
		return (DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'training` WHERE `id`='.(int)$id.' AND `typeid`="'.CONF_WK_TYPID.'" LIMIT 1')->fetchColumn() > 0);
	}
}