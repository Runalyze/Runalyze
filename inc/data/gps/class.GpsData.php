<?php
/**
 * This file contains class::GpsData
 * @package Runalyze\Data\GPS
 */
/**
 * GPS data
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS
 */
class GpsData {
	/**
	 * Only every n-th point will be taken for the elevation
	 * @var int
	 */
	public static $everyNthElevationPoint = 5;

	/**
	 * Number of steps being recognized for zones
	 * @var int 
	 */
	public static $NUM_STEPS_FOR_ZONES = 200;

	/**
	 * Boolean flag: Use original elevation?
	 * @var boolean
	 */
	private static $USES_ORIGINAL_ELEVATION = false;

	/**
	 * Array with all information for time
	 * @var array
	 */
	private $arrayForTime = array();

	/**
	* Array with all information for latitude
	* @var array
	*/
	private $arrayForLatitude = array();

	/**
	* Array with all information for longitude
	* @var array
	*/
	private $arrayForLongitude = array();

	/**
	* Array with all information for elevation
	* @var array
	*/
	private $arrayForElevation = array();

	/**
	* Array with all information for original elevation
	* @var array
	*/
	private $arrayForElevationOriginal = array();

	/**
	* Array with all information for distance
	* @var array
	*/
	private $arrayForDistance = array();

	/**
	* Array with all information for heartrate
	* @var array
	*/
	private $arrayForHeartrate = array();

	/**
	* Array with all information for Pace
	* @var array
	*/
	private $arrayForPace = array();

	/**
	* Array with all information for Cadence
	* @var array
	*/
	private $arrayForCadence = array();

	/**
	* Array with all information for Power
	* @var array
	*/
	private $arrayForPower = array();

	/**
	* Array with all information for Temperature
	* @var array
	*/
	private $arrayForTemperature = array();

	/**
	* Size of all arrays
	* @var int
	*/
	private $arraySizes = 0;

	/**
	 * Default index
	 * @var int
	 */
	static $DEFAULT_INDEX = -1;

	/**
	* Index of current step
	* @var int
	*/
	private $arrayIndex = -1;

	/**
	* Index of last step
	* @var int
	*/
	private $arrayLastIndex = -1;

	/**
	 * Step size for each step
	 * @var int
	 */
	private $stepSize = 1;

	/**
	 * Cache object
	 * @var GpsDataCache
	 */
	protected $Cache = null;

	/**
	 * Set flag to use original elevation
	 */
	static public function useOriginalElevation() {
		self::$USES_ORIGINAL_ELEVATION = true;
	}

	/**
	 * Set flag to use corrected elevation
	 */
	static public function useCorrectedElevation() {
		self::$USES_ORIGINAL_ELEVATION = false;
	}

	/**
	 * Constructor
	 */
	public function __construct($TrainingDataAsArray) {
		$this->addMissingKeysToArray($TrainingDataAsArray);

		$this->arrayForTime              = $this->loadArrayDataFrom($TrainingDataAsArray['arr_time']);
		$this->arrayForLatitude          = $this->loadArrayDataFrom($TrainingDataAsArray['arr_lat']);
		$this->arrayForLongitude         = $this->loadArrayDataFrom($TrainingDataAsArray['arr_lon']);
		$this->arrayForElevation         = $this->loadArrayDataFrom($TrainingDataAsArray['arr_alt']);
		$this->arrayForElevationOriginal = $this->loadArrayDataFrom($TrainingDataAsArray['arr_alt_original']);
		$this->arrayForDistance          = $this->loadArrayDataFrom($TrainingDataAsArray['arr_dist']);
		$this->arrayForHeartrate         = $this->loadArrayDataFrom($TrainingDataAsArray['arr_heart']);
		$this->arrayForPace              = $this->loadArrayDataFrom($TrainingDataAsArray['arr_pace']);
		$this->arrayForCadence           = $this->loadArrayDataFrom($TrainingDataAsArray['arr_cadence']);
		$this->arrayForPower             = $this->loadArrayDataFrom($TrainingDataAsArray['arr_power']);
		$this->arrayForTemperature       = $this->loadArrayDataFrom($TrainingDataAsArray['arr_temperature']);
		$this->arraySizes                = max(count($this->arrayForTime), count($this->arrayForLatitude), count($this->arrayForDistance));

		if (isset($TrainingDataAsArray['gps_cache_object']))
			$this->initCache($TrainingDataAsArray['id'], $TrainingDataAsArray['gps_cache_object']);
		else
			$this->initCache(0, false);
	}

	/**
	 * Add missing keys to array
	 * @param array $array training data
	 */
	private function addMissingKeysToArray(array &$array) {
		$keys = array(
			'arr_time',
			'arr_lat',
			'arr_lon',
			'arr_alt',
			'arr_alt_original',
			'arr_dist',
			'arr_heart',
			'arr_pace',
			'arr_cadence',
			'arr_power',
			'arr_temperature'
		);

		foreach ($keys as $key)
			if (!isset($array[$key]))
				$array[$key] = '';
	}

	/**
	 * Load array for internal data from string
	 * @param string $string
	 * @return array
	 */
	private function loadArrayDataFrom($string) {
		$array = explode(DataObject::$ARR_SEP, $string);

		return $array;
	}

	/**
	 * Init cache
	 * @param int $TrainingID
	 * @param mixed $String [optional]
	 */
	private function initCache($TrainingID, $String = null) {
		$this->Cache = new GpsDataCache($TrainingID, $String);

		if ($this->Cache->isEmpty() && $String !== false) {
			$this->Cache->set('pulse_zones', $this->getPulseZonesAsFilledArrays());
			$this->Cache->set('pace_zones', $this->getPaceZonesAsFilledArrays());
			$this->Cache->set('rounds', $this->getRoundsAsFilledArray());

			$PlotData = $this->getPlotDataForAllPlots();
			foreach ($PlotData as $key => $value)
				$this->Cache->set('plot_'.$key, $value);

			$this->Cache->saveInDatabase();
		}
	}

	/**
	 * Get cache
	 * @return GpsDataCache
	 */
	public function getCache() {
		return $this->Cache;
	}

	/**
	 * Start loop through all steps
	 */
	public function startLoop() {
		$this->arrayIndex = 0;
		$this->stepSize   = 1;

		if (empty($this->arrayForTime) && empty($this->arrayForDistance))
			$this->arraySizes = max(
				count($this->arrayForLatitude),
				count($this->arrayForLongitude),
				count($this->arrayForElevation),
				count($this->arrayForElevationOriginal),
				count($this->arrayForHeartrate),
				count($this->arrayForPace),
				count($this->arrayForCadence),
				count($this->arrayForPower),
				count($this->arrayForTemperature)
			);
	}

	/**
	 * Set individual step-size
	 * @param int $size
	 */
	public function setStepSize($size) {
		$this->stepSize = (int)$size;

		if ($this->stepSize < 1)
			$this->stepSize = 1;
	}

	/**
	 * Is the iterator at default index?
	 * @return bool
	 */
	protected function loopIsAtDefaultIndex() {
		return ($this->arrayIndex == self::$DEFAULT_INDEX);
	}

	/**
	 * Has the iterator reached the end of each array?
	 * @return bool
	 */
	protected function loopIsAtEnd() {
		return ($this->arrayIndex >= $this->arraySizes-1);
	}

	/**
	 * Go to next step if possible
	 * @return bool
	 */
	public function nextStep() {
		$this->arrayLastIndex = $this->arrayIndex;

		if ($this->loopIsAtEnd())
			return false;

		$this->arrayIndex += $this->stepSize;

		if ($this->loopIsAtEnd())
			$this->arrayIndex = $this->arraySizes-1;

		return true;
	}

	/**
	 * Go to next kilometer if possible
	 * If there is no distance time (5:00 as 1 km) is taken
	 * @param double $distance
	 * @return bool
	 */
	public function nextKilometer($distance = 1) {
		$timeStepFromDistance = $distance * 5 * 60;
		$this->arrayLastIndex = $this->arrayIndex;

		if ($this->loopIsAtEnd())
			return false;

		if ($this->plotUsesTimeOnXAxis())
			while ($this->currentTimeStep($timeStepFromDistance) == floor($this->arrayForTime[$this->arrayLastIndex]/$timeStepFromDistance)*$timeStepFromDistance)
				$this->arrayIndex++;
		else
			while (!$this->loopIsAtEnd() && $this->currentKilometer($distance) == floor($this->arrayForDistance[$this->arrayLastIndex]/$distance)*$distance)
				$this->arrayIndex++;

		return true;
	}

	/**
	 * Go to given distance
	 * @param double $distance
	 * @return bool
	 */
	public function goToDistance($distance) {
		$this->arrayLastIndex = $this->arrayIndex;

		if ($this->loopIsAtEnd())
			return false;

		while (!$this->loopIsAtEnd() && $this->arrayForDistance[$this->arrayIndex] < $distance)
			$this->arrayIndex++;

		return true;
	}

	/**
	 * Go to end
	 */
	public function goToEnd() {
		$this->arrayLastIndex = $this->arrayIndex;

		$this->arrayIndex = $this->arraySizes-1;
	}

	/**
	 * Get the current kilometer
	 * @param double $distance
	 * @return int
	 */
	public function currentKilometer($distance = 1) {
		if ($this->loopIsAtDefaultIndex())
			return 0;

		if ($this->loopIsAtEnd())
			return end($this->arrayForDistance);

		return floor($this->arrayForDistance[$this->arrayIndex]/$distance)*$distance;
	}

	/**
	 * Get the time step (if no distance is available, default: 5 minutes)
	 * @param int $timestep in seconds
	 * @return int
	 */
	public function currentTimeStep($timestep = 300) {
		if ($this->loopIsAtDefaultIndex())
			return 0;

		if ($this->loopIsAtEnd())
			return end($this->arrayForTime);

		return floor($this->arrayForTime[$this->arrayIndex]/$timestep)*$timestep;
	}

	/**
	 * Get total distance
	 * @return float
	 */
	public function getTotalDistance() {
		return end($this->arrayForDistance);
	}

	/**
	 * Get total time
	 * @return int
	 */
	public function getTotalTime() {
		return end($this->arrayForTime);
	}

	/**
	 * Are information for pace available?
	 */
	public function hasPaceData() {
		return !empty($this->arrayForPace) && $this->getTotalDistance() > 0;
	}

	/**
	 * Are information for elevation available?
	 */
	public function hasElevationData() {
		if (self::$USES_ORIGINAL_ELEVATION)
			return $this->hasElevationDataOriginal();

		return !empty($this->arrayForElevation) && max($this->arrayForElevation) > 0;
	}

	/**
	 * Are information for original elevation available?
	 */
	public function hasElevationDataOriginal() {
		return !empty($this->arrayForElevationOriginal) && max($this->arrayForElevationOriginal) > 0;
	}

	/**
	 * Are information for heartrate available?
	 */
	public function hasHeartrateData() {
		return !empty($this->arrayForHeartrate) && (max($this->arrayForHeartrate) > 0.5*HF_MAX);
	}

	/**
	 * Are information for latitude/longitude available?
	 */
	public function hasPositionData() {
		return !empty($this->arrayForLatitude) && (count($this->arrayForLongitude) > 1)
			&& (max($this->arrayForLatitude) > 0 || min($this->arrayForLatitude) < 0);
	}

	/**
	 * Get bounds
	 * @return array lat.min/lat.max/lng.min/lng.max
	 */
	public function getBounds() {
		return array(
			'lat.min' => min($this->arrayForLatitude),
			'lat.max' => max($this->arrayForLatitude),
			'lng.min' => min($this->arrayForLongitude),
			'lng.max' => max($this->arrayForLongitude)
		);
	}

	/**
	 * Are information for elevation available?
	 */
	public function hasDistanceData() {
		return !empty($this->arrayForDistance) && max($this->arrayForDistance) > 0;
	}

	/**
	 * Are information for elevation available?
	 */
	public function hasTimeData() {
		return !empty($this->arrayForTime) && max($this->arrayForTime) > 0;
	}

	/**
	 * Are information for elevation available?
	 */
	public function hasCadenceData() {
		return !empty($this->arrayForCadence) && max($this->arrayForCadence) > 0;
	}

	/**
	 * Are information for elevation available?
	 */
	public function hasPowerData() {
		return !empty($this->arrayForPower) && max($this->arrayForPower) > 0;
	}

	/**
	 * Are information for elevation available?
	 */
	public function hasTemperatureData() {
		return !empty($this->arrayForTemperature) && max($this->arrayForTemperature) > 0;
	}

	/**
	 * Get a value from one of the internal arrays
	 * @param array $array
	 * @return int
	 */
	protected function get(&$array) {
		if (isset($array[$this->arrayIndex]))
			return $array[$this->arrayIndex];

		Error::getInstance()->addWarning('Array offset in class::GpsData.');

		return 0;
	}

	/**
	 * Get the difference between the values of current/last step
	 * @param array $array
	 * @param bool $startWithZero
	 * @return int
	 */
	protected function getOfStep(&$array, $startWithZero = false) {
		if (empty($array) || !isset($array[$this->arrayIndex]))
			return 0;

		if (!isset($array[$this->arrayLastIndex]))
			return $array[$this->arrayIndex];

		if ($this->arrayLastIndex == 0 && $startWithZero)
			return $array[$this->arrayIndex];

		return ($array[$this->arrayIndex] - $array[$this->arrayLastIndex]);
	}

	/**
	 * Get average of complete step
	 * @param array $array
	 * @return float
	 */
	protected function getAverageOfStep(&$array) {
		if (empty($array) || !isset($array[$this->arrayIndex]))
			return 0;

		$stepArray = array_slice($array, $this->arrayLastIndex, ($this->arrayIndex - $this->arrayLastIndex), true);
		$stepArray = array_filter($stepArray);

		if (count($stepArray) == 0)
			return 0;

		return (array_sum($stepArray) / count($stepArray));
	}

	/**
	 * Get maximum of complete step
	 * @param array $array
	 * @return float
	 */
	protected function getMaximumOfStep(&$array) {
		if (empty($array) || !isset($array[$this->arrayIndex]))
			return 0;

		$stepArray = array_slice($array, $this->arrayLastIndex, ($this->arrayIndex - $this->arrayLastIndex), true);
		$stepArray = array_filter($stepArray);

		if (count($stepArray) == 0)
			return 0;

		return max($stepArray);
	}

	/**
	 * Get seconds since last step
	 */
	public function getTimeOfStep() {
		return $this->getOfStep($this->arrayForTime, true);
	}

	/**
	 * Get distance since last step
	 */
	public function getDistanceOfStep() {
		return $this->getOfStep($this->arrayForDistance, true);
	}

	/**
	 * Get elevation since last step
	 */
	public function getElevationOfStep() {
		if (self::$USES_ORIGINAL_ELEVATION)
			return $this->getOfStep($this->arrayForElevationOriginal);

		return $this->getOfStep($this->arrayForElevation);
	}

	/**
	 * Get average heartrate since last step
	 */
	public function getAverageHeartrateOfStep() {
		return round($this->getAverageOfStep($this->arrayForHeartrate));
	}

	/**
	 * Get maximum heartrate since last step
	 */
	public function getMaximumHeartrateOfStep() {
		return round($this->getMaximumOfStep($this->arrayForHeartrate));
	}

	/**
	 * Get average pace since last step
	 */
	public function getAveragePaceOfStep() {
		return round($this->getAverageOfStep($this->arrayForPace));
	}

	/**
	 * Get average pace since last step
	 */
	public function getAverageCadenceOfStep() {
		return round($this->getAverageOfStep($this->arrayForCadence));
	}

	/**
	 * Get average pace since last step
	 */
	public function getAveragePowerOfStep() {
		return round($this->getAverageOfStep($this->arrayForPower));
	}

	/**
	 * Get average pace since last step
	 */
	public function getAverageTemperatureOfStep() {
		return round($this->getAverageOfStep($this->arrayForTemperature));
	}

	/**
	 * Get average elevation since last step
	 */
	public function getAverageElevationOfStep() {
		return round($this->getAverageOfStep($this->arrayForElevation));
	}

	/**
	 * Get average pace of training in seconds
	 * @return int 
	 */
	public function getAveragePace() {
		if ($this->getTotalDistance() > 0)
			return round($this->getTotalTime()/$this->getTotalDistance());

		return 0;
	}

	/**
	 * Get current time
	 */
	public function getTime() {
		return $this->get($this->arrayForTime);
	}

	/**
	 * Get current latitude
	 */
	public function getLatitude() {
		return $this->get($this->arrayForLatitude);
	}

	/**
	 * Get current longitude
	 */
	public function getLongitude() {
		return $this->get($this->arrayForLongitude);
	}

	/**
	 * Get current elevation
	 */
	public function getElevation() {
		if (self::$USES_ORIGINAL_ELEVATION)
			return $this->get($this->arrayForElevationOriginal);

		return $this->get($this->arrayForElevation);
	}

	/**
	 * Get current distance
	 */
	public function getDistance() {
		return $this->get($this->arrayForDistance);
	}

	/**
	 * Get current heartrate
	 */
	public function getHeartrate() {
		return $this->get($this->arrayForHeartrate);
	}

	/**
	 * Get current pace
	 */
	public function getPace() {
		return $this->get($this->arrayForPace);
	}

	/**
	 * Get current cadence
	 */
	public function getCadence() {
		return $this->get($this->arrayForCadence);
	}

	/**
	 * Get current power
	 */
	public function getPower() {
		return $this->get($this->arrayForPower);
	}

	/**
	 * Get current temperature
	 */
	public function getTemperature() {
		return $this->get($this->arrayForTemperature);
	}

	/**
	 * Get maximum of elevation
	 */
	public function getMaximumOfElevation() {
		if (self::$USES_ORIGINAL_ELEVATION && !empty($this->arrayForElevationOriginal))
			return max($this->arrayForElevationOriginal);

		if (!empty($this->arrayForElevation))
			return max($this->arrayForElevation);

		return 0;
	}

	/**
	 * Get minimum of elevation
	 */
	public function getMinimumOfElevation() {
		if (self::$USES_ORIGINAL_ELEVATION && !empty($this->arrayForElevationOriginal))
			return min($this->arrayForElevationOriginal);

		if (!empty($this->arrayForElevation))
			return min($this->arrayForElevation);

		return 0;
	}

	/**
	 * Get elevation up of current step
	 * @return int
	 */
	public function getElevationUpOfStep() {
		$UpDown = $this->getElevationUpDownOfStep();

		return $UpDown[0];
	}

	/**
	 * Get elevation down of current step
	 * @return int
	 */
	public function getElevationDownOfStep() {
		$UpDown = $this->getElevationUpDownOfStep();

		return $UpDown[1];
	}

	/**
	 * Get currently used elevation array
	 * @return array
	 */
	private function getCurrentlyUsedElevationArray() {
		if (self::$USES_ORIGINAL_ELEVATION)
			return $this->arrayForElevationOriginal;

		return $this->arrayForElevation;
	}

	/**
	 * Get array with up/down of current step
	 * @parameter boolean $complete
	 * @return array
	 */
	public function getElevationUpDownOfStep($complete = false) {
		$elevationArray = $this->getCurrentlyUsedElevationArray();

		if (empty($elevationArray) || (!$complete && !isset($elevationArray[$this->arrayIndex])))
			return array(0, 0);

		$stepArray = $complete ? $elevationArray : array_slice($elevationArray, $this->arrayLastIndex, ($this->arrayIndex - $this->arrayLastIndex));

		$Calculator = new ElevationCalculator($stepArray);
		$Calculator->calculateElevation();

		return array($Calculator->getElevationUp(), $Calculator->getElevationDown());
	}

	/**
	 * Get pulse zones as sorted array filled with information for time, distance, pace-sum, num
	 * @return array
	 */
	public function getPulseZonesAsFilledArrays() {
		if (!$this->hasHeartrateData())
			return array();

		if (!$this->Cache->isEmpty())
			return $this->Cache->get('pulse_zones');

		$Zones = array();
		$this->startLoop();
		$this->setStepSize( round($this->arraySizes / self::$NUM_STEPS_FOR_ZONES) );

		while ($this->nextStep()) {
			$zone = ceil(100 * $this->getAverageHeartrateOfStep() / Helper::getHFmax() / 10);
		
			if (!isset($Zones[$zone]))
				$Zones[$zone] = array('time' => 0, 'distance' => 0, 'pace-sum' => 0, 'num' => 0);
		
			$Zones[$zone]['time']     += $this->getTimeOfStep();
			$Zones[$zone]['distance'] += $this->getDistanceOfStep();
			$Zones[$zone]['pace-sum'] += $this->getAveragePaceOfStep();
			$Zones[$zone]['num']++;
		}
		
		ksort($Zones);

		return $Zones;
	}

	/**
	 * Get pace zones as sorted array filled with information for time, distance, hf-sum, num
	 * @return array
	 */
	public function getPaceZonesAsFilledArrays() {
		if (!$this->hasPaceData())
			return array();

		if (!$this->Cache->isEmpty())
			return $this->Cache->get('pace_zones');

		$Zones = array();
		$this->startLoop();
		$this->setStepSize( round($this->arraySizes / self::$NUM_STEPS_FOR_ZONES) );

		while ($this->nextStep()) {
			$zone = floor($this->getAveragePaceOfStep() / 60);

			if ($zone >= 10)
				$zone = 10;

			if (!isset($Zones[$zone]))
				$Zones[$zone] = array('time' => 0, 'distance' => 0, 'hf-sum' => 0, 'num' => 0);

			$Zones[$zone]['time']     += $this->getTimeOfStep();
			$Zones[$zone]['distance'] += $this->getDistanceOfStep();
			$Zones[$zone]['hf-sum']   += $this->getAverageHeartrateOfStep();
			$Zones[$zone]['num']++;
		}

		krsort($Zones);
		
		return $Zones;
	}

	/**
	 * Get rounds as sorted array
	 * 
	 * Filled with information for time, distance, km, s, heartrate, hm-up, hm-down
	 * @param mixed $distance [optional] can be double or array
	 * @return array
	 */
	public function getRoundsAsFilledArray($distance = 1) {
		if (!$this->Cache->isEmpty() && $distance == 1)
			return $this->Cache->get('rounds');

		$rounds = array();

		if (!$this->hasDistanceData() || !$this->hasTimeData())
			return array();
		
		$this->startLoop();

		if (is_array($distance)) {
			foreach ($distance as $dist) {
				$this->goToDistance($dist);
				$rounds[] = $this->getCurrentRoundAsFilledArray();
			}

			$this->goToEnd();
			$rounds[] = $this->getCurrentRoundAsFilledArray();
		} else {
			while ($this->nextKilometer($distance))
				$rounds[] = $this->getCurrentRoundAsFilledArray();
		}

		return $rounds;
	}

	/**
	 * Get current round as filled array
	 * @return array
	 */
	private function getCurrentRoundAsFilledArray() {
		return array(
			'time'      => $this->getTime(),
			'distance'  => $this->getDistance(),
			'km'        => $this->getDistanceOfStep(),
			's'         => $this->getTimeOfStep(),
			'heartrate' => $this->getAverageHeartrateOfStep(),
			'hm-up'     => $this->getElevationUpOfStep(),
			'hm-down'   => $this->getElevationDownOfStep(),
		);
	}

	/**
	 * Does the plot uses time on x-axis? (due to missing distance-values)
	 * @return boolean
	 */
	public function plotUsesTimeOnXAxis() {
		return $this->getTotalDistance() == 0;
	}

	/**
	 * Get plot data for a given key
	 * @param string $key
	 * @return array
	 */
	protected function getPlotDataFor($key) {
		if (!$this->Cache->isEmpty()) {
			$result = $this->Cache->get('plot_'.$key);

			if (!is_null($result))
				return $result;
		}

		$Data = array();
		$this->startLoop();
		$this->setStepSizeForPlotData();
		while ($this->nextStepForPlotData()) {
			if (!$this->hasDistanceData() && !$this->hasTimeData()) {
				$Data[] = $this->getCurrentPlotDataFor($key);
			} else {
				if ($this->plotUsesTimeOnXAxis())
					$index = (string)($this->getTime()).'000';
				else
					$index = (string)($this->getDistance());

				$Data[$index] = $this->getCurrentPlotDataFor($key);
			}
		}

		return $Data;
	}

	/**
	 * Get data for all plots
	 * @return type 
	 */
	protected function getPlotDataForAllPlots() {
		$index = 0;
		$Data = array();
		$this->startLoop();
		$this->setStepSizeForPlotData();
		while ($this->nextStepForPlotData()) {
			if (!$this->hasDistanceData() && !$this->hasTimeData()) {
				$index++;
			} else {
				if ($this->plotUsesTimeOnXAxis())
					$index = (string)($this->getTime()).'000';
				else
					$index = (string)($this->getDistance());
			}

			$Heartrate = $this->getCurrentPlotDataFor('heartrate');
			$Data['pace'][$index]                = $this->getCurrentPlotDataFor('pace');
			$Data['cadence'][$index]             = $this->getCurrentPlotDataFor('cadence');
			$Data['power'][$index]               = $this->getCurrentPlotDataFor('power');
			$Data['temperature'][$index]         = $this->getCurrentPlotDataFor('temperature');
			$Data['elevation'][$index]           = $this->getCurrentPlotDataFor('elevation');
			$Data['heartrate'][$index]           = $Heartrate;
			$Data['heartrate100'][$index]        = 100*$Heartrate/HF_MAX;
			$Data['heartrate100reserve'][$index] = Running::PulseInPercentReserve($Heartrate);
		}

		return $Data;
	}

	/**
	 * Get next step for plot data
	 * @return bool 
	 */
	protected function nextStepForPlotData() {
		if (Configuration::ActivityView()->plotPrecision()->byDistance()) {
			return $this->nextKilometer( Configuration::ActivityView()->plotPrecision()->distanceStep()*1000 );
		}

		return $this->nextStep();
	}

	/**
	 * Set step size for plot data
	 */
	protected function setStepSizeForPlotData() {
		if (Configuration::ActivityView()->plotPrecision()->byPoints()) {
			$Points = Configuration::ActivityView()->plotPrecision()->numberOfPoints();

			if ($this->arraySizes > $Points)
				$this->setStepSize( round($this->arraySizes / $Points) );
		}
	}

	/**
	 * Get current plot data
	 * @param string $key 
	 * @return mixed
	 */
	private function getCurrentPlotDataFor($key) {
		switch ($key) {
			case "elevation":
				$value = $this->getAverageElevationOfStep();
				break;
			case "heartrate100":
				$value = 100*$this->getAverageHeartrateOfStep()/HF_MAX;
				break;
			case "heartrate100reserve":
				$value = Running::PulseInPercentReserve($this->getAverageHeartrateOfStep());
				break;
			case "heartrate":
				$value = $this->getAverageHeartrateOfStep();
				break;
			case "pace":
				$value = $this->getAveragePaceOfStep();
				break;
			case "cadence":
				$value = $this->getAverageCadenceOfStep();
				break;
			case "power":
				$value = $this->getAveragePowerOfStep();
				break;
			case "temperature":
				$value = $this->getAverageTemperatureOfStep();
				break;
			default:
				$value = 0;
		}

		if ($value < 0)
			$value = 0;

		return $value;
	}

	/**
	 * Get array as plot-data for elevation
	 */
	public function getPlotDataForElevation() {
		return $this->getPlotDataFor('elevation');
	}

	/**
	 * Get array as plot-data for heartrate
	 */
	public function getPlotDataForHeartrate($inPercent = false) {
		if ($inPercent)
			return $this->getPlotDataFor('heartrate100');

		return $this->getPlotDataFor('heartrate');
	}

	/**
	 * Get array as plot-data for heartrate in percent
	 */
	public function getPlotDataForHeartrateInPercent() {
		if (Configuration::General()->heartRateUnit()->isHRreserve())
			return $this->getPlotDataForHeartrateInPercentReserve();

		return $this->getPlotDataForHeartrate(true);
	}

	/**
	 * Get array as plot-data for heartrate in percent
	 */
	public function getPlotDataForHeartrateInPercentReserve() {
		return $this->getPlotDataFor('heartrate100reserve');
	}

	/**
	 * Get array as plot-data for pace
	 */
	public function getPlotDataForPace() {
		return $this->getPlotDataFor('pace');
	}

	/**
	 * Get array as plot-data for cadence
	 */
	public function getPlotDataForCadence() {
		return $this->getPlotDataFor('cadence');
	}

	/**
	 * Get array as plot-data for power
	 */
	public function getPlotDataForPower() {
		return $this->getPlotDataFor('power');
	}

	/**
	 * Get array as plot-data for temperature
	 */
	public function getPlotDataForTemperature() {
		return $this->getPlotDataFor('temperature');
	}

	/**
	 * Correct the elevation data and return new array
	 * @return mixed
	 */
	public function getElevationCorrection() {
		if (!$this->hasPositionData())
			return;

		try {
			$ElevationCorrector = new ElevationCorrector();
			$ElevationCorrector->correctElevation($this->arrayForLatitude, $this->arrayForLongitude);

			$elevationArray = $ElevationCorrector->getCorrectedElevation();

			if (!empty($elevationArray)) {
				$this->arrayForElevation = $elevationArray;
				$this->correctInvalidElevationValues();

				return $this->arrayForElevation;
			}
		} catch (Exception $Exception) {
			// TODO: Make this exception somehow visible
			Error::getInstance()->addError($Exception->getMessage());
		}

		return false;
	}

	/**
	 * Correct invalid values for elevation in case of missing latitude/longitude
	 */
	private function correctInvalidElevationValues() {
		$this->startLoop();
		$this->correctInvalidElevationValuesAtCurrentPoint();

		while ($this->nextStep())
			$this->correctInvalidElevationValuesAtCurrentPoint();

		if ($this->arrayForElevation[0] == 0) {
			array_filter($this->arrayForElevation, 'GpsData_Filter_Zero');
			$min = reset($this->arrayForElevation);
			array_walk($this->arrayForElevation, 'GpsData_Walk_Replace_Zero', $min);
		}
	}

	/**
	 * Correct invalid values at current point 
	 */
	private function correctInvalidElevationValuesAtCurrentPoint() {
		if ($this->getLatitude() == 0 || $this->getLongitude() == 0 || $this->getElevation() <= 0) {
			if (isset($this->arrayForLatitude[$this->arrayLastIndex])) {
				$this->arrayForLatitude[$this->arrayIndex] = $this->arrayForLatitude[$this->arrayLastIndex];
				$this->arrayForLongitude[$this->arrayIndex] = $this->arrayForLongitude[$this->arrayLastIndex];
				$this->arrayForElevation[$this->arrayIndex] = $this->arrayForElevation[$this->arrayLastIndex];
			} else {
				$this->arrayForElevation[$this->arrayIndex] = 0;
			}
		}
	}

	/**
	 * Calculate complete elevation
	 * 
	 * Can return array($elevation, $up, $down)
	 * @param boolean $returnArray
	 * @return int|array
	 */
	public function calculateElevation($returnArray = false) {
		if (!$this->hasElevationData())
			return ($returnArray ? array(0, 0, 0) : 0);

		$elevationArrayToUse = $this->getCurrentlyUsedElevationArray();
		$minimumElevation    = (min($elevationArrayToUse) > 0) ? max($elevationArrayToUse) - min($elevationArrayToUse) : 0;
		$elevationArray      = $this->getElevationUpDownOfStep(true);
		$value = max($minimumElevation, $elevationArray[0], $elevationArray[1]);

		if ($returnArray)
			return array($value, $elevationArray[0], $elevationArray[1]);

		return $value;
	}

	/**
	 * Calculate difference in elevation
	 * @return int
	 */
	public function getElevationDifference() {
		if (!$this->hasElevationData())
			return 0;

		$elevationArrayToUse = $this->getCurrentlyUsedElevationArray();

		return end($elevationArrayToUse) - $elevationArrayToUse[0];
	}

	/**
	 * Calculate average virtual power
	 * @return array
	 */
	public function averagePower() {
		if ($this->arraySizes == 0)
			return 0;

		return array_sum($this->arrayForPower) / $this->arraySizes;
	}

	/**
	 * Calculate virtual power
	 * @see http://www.blog.ultracycle.net/2010/05/cycling-power-calculations
	 * @return array
	 */
	public function calculatePower() {
		if (!$this->hasDistanceData() || !$this->hasTimeData())
			return array();

		/* same step size as elevation, since we use that data
		 * to calculate grade
		 */
		$everyNthPoint  = self::$everyNthElevationPoint * ceil($this->arraySizes/1000);
		$n              = $everyNthPoint;
		$power          = array();
		$distance       = 0;
		$grade          = 0;
		$calcGrade      = $this->hasElevationData();

		$PowerFactor = 1.5; /* XXX CONFIG */

		$Wkg  = 75; /* XXX CONFIG */
		$Crr  = 0.004; /* XXX CONFIG */
		$g    = 9.8;
		$Frl  = $Wkg * $g * $Crr;

		$A    = 0.5;
		$Cw   = 0.5;
		$Rho  = 1.226; /* XXX CONFIG/COMPUTE? */
		$Fwpr = 0.5 * $A * $Cw * $Rho;

		$Fslp = $Wkg * $g;

		for ($i = 0; $i < $this->arraySizes-1; $i++) {
			if ($i%$everyNthPoint == 0) {
				if ($i+$n > $this->arraySizes-1)
					$n = $this->arraySizes-$i-1;
				$distance = ($this->arrayForDistance[$i+$n]-$this->arrayForDistance[$i])*1000;
				if ($distance == 0 || !$calcGrade)
					$grade = 0;
				else
					$grade = ($this->arrayForElevation[$i+$n]-$this->arrayForElevation[$i])/$distance;
			}

			$distance = $this->arrayForDistance[$i+1]-$this->arrayForDistance[$i];
			$time = $this->arrayForTime[$i+1]-$this->arrayForTime[$i];
			if ($time > 0) {
				$Vmps = $distance*1000/$time;
				$Fw   = $Fwpr * $Vmps * $Vmps;
				$Fsl  = $Fslp * $grade;
				$power[] = round(max($PowerFactor * ($Frl + $Fw + $Fsl) * $Vmps, 0));
				//error_log("(".$Frl." + ".$Fw." + ".$Fsl.") * ".$Vmps." = ".$power[$i]);
			} else {
				$power[] = 0;
			}
		}

		$power[] = $power[$this->arraySizes-2]; /* XXX */

		$this->arrayForPower = $power;

		return $power;
	}

	/**
	 * Compress data to a minimum
	 */
	public function compressData() {
		// TODO
	}

	/**
	 * Calculate distance of current step from latitude/longitude
	 * @return double
	 */
	public function getCalculatedDistanceOfStep() {
		return self::distance(
			$this->arrayForLatitude[$this->arrayLastIndex],
			$this->arrayForLongitude[$this->arrayLastIndex],
			$this->arrayForLatitude[$this->arrayIndex],
			$this->arrayForLongitude[$this->arrayIndex]);
	}

	/**
	 * Calculate distance between two coordinates
	 * @param double $lat1
	 * @param double $lon1
	 * @param double $lat2
	 * @param double $lon2
	 * @return double
	 */
	static public function distance($lat1, $lon1, $lat2, $lon2) {
		$rad1 = deg2rad($lat1);
		$rad2 = deg2rad($lat2);
		$dist = sin($rad1) * sin($rad2) +  cos($rad1) * cos($rad2) * cos(deg2rad($lon1 - $lon2)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;

		if (is_nan($miles))
			return 0;
	
		return ($miles * 1.609344);
	}
}

/**
 * Filter function to filter all negative/zero values out
 * @param mixed $value
 * @return boolean
 */
function GpsData_Filter_Zero($value) {
	return $value > 0;
}

/**
 * Walk function to replace zeros/negative values with another value
 * @param mixed $value
 * @param int $key
 * @param float $newValueForZeroes 
 */
function GpsData_Walk_Replace_Zero(&$value, $key, $newValueForZeros) {
	if ($value <= 0)
		$value = $newValueForZeros;
}