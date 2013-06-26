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
	 * Minimal difference per step to be recognized for elevation data
	 * @var int
	 */
	public static $minElevationDiff = CONF_ELEVATION_MIN_DIFF;

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
	 * Constructor
	 */
	public function __construct($TrainingDataAsArray) {
		$this->addMissingKeysToArray($TrainingDataAsArray);

		$this->arrayForTime        = $this->loadArrayDataFrom($TrainingDataAsArray['arr_time']);
		$this->arrayForLatitude    = $this->loadArrayDataFrom($TrainingDataAsArray['arr_lat']);
		$this->arrayForLongitude   = $this->loadArrayDataFrom($TrainingDataAsArray['arr_lon']);
		$this->arrayForElevation   = $this->loadArrayDataFrom($TrainingDataAsArray['arr_alt']);
		$this->arrayForDistance    = $this->loadArrayDataFrom($TrainingDataAsArray['arr_dist']);
		$this->arrayForHeartrate   = $this->loadArrayDataFrom($TrainingDataAsArray['arr_heart']);
		$this->arrayForPace        = $this->loadArrayDataFrom($TrainingDataAsArray['arr_pace']);
		$this->arrayForCadence     = $this->loadArrayDataFrom($TrainingDataAsArray['arr_cadence']);
		$this->arrayForPower       = $this->loadArrayDataFrom($TrainingDataAsArray['arr_power']);
		$this->arrayForTemperature = $this->loadArrayDataFrom($TrainingDataAsArray['arr_temperature']);
		$this->arraySizes          = max(count($this->arrayForTime), count($this->arrayForLatitude));

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

		if (count($array) == 1)
			return array();

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

			$GMap = new Gmap($TrainingID, $this);
			$GMap->setCacheTo($this->Cache);

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
	 * Are information for pace available?
	 */
	public function hasPaceData() {
		return !empty($this->arrayForPace) && $this->getTotalDistance() > 0;
	}

	/**
	 * Are information for elevation available?
	 */
	public function hasElevationData() {
		return !empty($this->arrayForElevation) && max($this->arrayForElevation) > 0;
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
		if ($this->hasPaceData())
			return round(array_sum($this->arrayForPace)/count($this->arrayForPace));

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
		if (!empty($this->arrayForElevation))
			return max($this->arrayForElevation);

		return 0;
	}

	/**
	 * Get minimum of elevation
	 */
	public function getMinimumOfElevation() {
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
	 * Get array with up/down of current step
	 * @parameter boolean $complete
	 * @return array
	 */
	public function getElevationUpDownOfStep($complete = false) {
		if (empty($this->arrayForElevation) || (!$complete && !isset($this->arrayForElevation[$this->arrayIndex])))
			return array(0, 0);

		$eachXthStep = 1;
		$positiveElevation = 0;
		$negativeElevation = 0;
		$stepArray = $complete ? $this->arrayForElevation : array_slice($this->arrayForElevation, $this->arrayLastIndex, ($this->arrayIndex - $this->arrayLastIndex));

		foreach ($stepArray as $i => $step) {
			if ($i >= $eachXthStep && $stepArray[$i] != 0 && $stepArray[$i-$eachXthStep] != 0 && $i%$eachXthStep == 0) {
				$elevationDifference = $stepArray[$i] - $stepArray[$i-$eachXthStep];
				$positiveElevation += ($elevationDifference >= self::$minElevationDiff) ? $elevationDifference : 0;
				$negativeElevation -= ($elevationDifference <= -1*self::$minElevationDiff) ? $elevationDifference : 0;
			}
		}

		return array($positiveElevation, $negativeElevation);
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
	 * Get rounds as sorted array filled with information for time, distance, km, s, heartrate, hm-up, hm-down
	 * @param double $distance [optional]
	 * @return array
	 */
	public function getRoundsAsFilledArray($distance = 1) {
		if (!$this->Cache->isEmpty() && $distance == 1)
			return $this->Cache->get('rounds');

		$rounds = array();

		if (!$this->hasDistanceData() || !$this->hasTimeData())
			return array();
		
		$this->startLoop();
		while ($this->nextKilometer($distance)) {
			$rounds[] = array(
				'time'      => $this->getTime(),
				'distance'  => $this->getDistance(),
				'km'        => $this->getDistanceOfStep(),
				's'         => $this->getTimeOfStep(),
				'heartrate' => $this->getAverageHeartrateOfStep(),
				'hm-up'     => $this->getElevationUpOfStep(),
				'hm-down'   => $this->getElevationDownOfStep(),
			);
		}

		return $rounds;
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
			return $this->Cache->get('plot_'.$key);
		}

		$Data = array();
		$this->startLoop();
		$this->setStepSizeForPlotData();
		while ($this->nextStepForPlotData()) {
			if ($this->plotUsesTimeOnXAxis())
				$index = (string)($this->getTime()).'000';
			else
				$index = (string)($this->getDistance());

			$Data[$index] = $this->getCurrentPlotDataFor($key);
		}

		return $Data;
	}

	/**
	 * Get data for all plots
	 * @return type 
	 */
	protected function getPlotDataForAllPlots() {
		$Data = array();
		$this->startLoop();
		$this->setStepSizeForPlotData();
		while ($this->nextStepForPlotData()) {
			if ($this->plotUsesTimeOnXAxis())
				$index = (string)($this->getTime()).'000';
			else
				$index = (string)($this->getDistance());

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
		switch (CONF_TRAINING_PLOT_PRECISION) {
			case '50m':
				return $this->nextKilometer(0.05);
			case '100m':
				return $this->nextKilometer(0.1);
			case '200m':
				return $this->nextKilometer(0.2);
			case '500m':
				return $this->nextKilometer(0.5);
			case '100points':
			case '200points':
			case '300points':
			case '400points':
			case '500points':
			case '750points':
			case '1000points':
			default:
				return $this->nextStep();
		}
	}

	/**
	 * Set step size for plot data
	 */
	protected function setStepSizeForPlotData() {
		switch (CONF_TRAINING_PLOT_PRECISION) {
			case '100points':
				$Points = 100;
				break;
			case '200points':
				$Points = 200;
				break;
			case '300points':
				$Points = 300;
				break;
			case '400points':
				$Points = 400;
				break;
			case '500points':
				$Points = 500;
				break;
			case '750points':
				$Points = 750;
				break;
			case '1000points':
				$Points = 1000;
				break;
			default:
				return;
		}

		if ($this->arraySizes > $Points)
			$this->setStepSize( round($this->arraySizes / $Points) );
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
		if (CONF_PULS_MODE == 'hfres')
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

		switch (CONF_TRAINING_ELEVATION_SERVER) {
			case 'google':
				$returnedArray = $this->getElevationCorrectionFromGoogle();
				break;
			case 'geonames':
			default:
				$returnedArray = $this->getElevationCorrectionFromGeonames();
		}

		if (is_array($returnedArray) && !empty($returnedArray))
			$this->arrayForElevation = $returnedArray;
		else
			return false;

		$this->correctInvalidElevationValues();

		return $this->arrayForElevation;
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
	 * Get elevation correction from GoogleMapsAPI
	 * @return array
	 */
	public function getElevationCorrectionFromGoogle() {
		$everyNthPoint  = self::$everyNthElevationPoint * ceil($this->arraySizes/1000);
		$numForEachCall = 20;
		$altitude       = array();
		$string         = array();

		for ($i = 0; $i < $this->arraySizes; $i++) {
			if ($i%$everyNthPoint == 0)
				$string[] = $this->arrayForLatitude[$i].','.$this->arrayForLongitude[$i];

			if (($i+1)%($numForEachCall*$everyNthPoint) == 0 || $i == $this->arraySizes-1) {
				$Xml = $this->getElevationFromGoogleAsSimpleXml($string);

				if ($Xml === false)
					return false;

				foreach ($Xml->xpath('result') as $Point) {
					for ($p = 0; $p < $everyNthPoint; $p++)
						$altitude[] = round((double)$Point->elevation);
				}

				$string = array();
			}
		}

		return $altitude;
	}

	/**
	 * Get elevation correction from Geonames
	 * @return array
	 */
	public function getElevationCorrectionFromGeonames() {
		$everyNthPoint  = self::$everyNthElevationPoint * ceil($this->arraySizes/1000);
		$numForEachCall = 20;
		$altitude = array();
		$lats     = array();
		$longs    = array();

		for ($i = 0; $i < $this->arraySizes; $i++) {
			if ($i%$everyNthPoint == 0) {
				$lats[]   = $this->arrayForLatitude[$i];
				$longs[]  = $this->arrayForLongitude[$i];
			}

			if (($i+1)%($numForEachCall*$everyNthPoint) == 0 || $i == $this->arraySizes-1) {
				$html = false;

				while ($html === false) {
					$html = Filesystem::getExternUrlContent('http://ws.geonames.org/srtm3?lats='.implode(',', $lats).'&lngs='.implode(',', $longs));
					if (substr($html,0,1) == '<')
						$html = false;
					else {
						$data = explode("\r\n", $html);
						$lats = array();
						$longs = array();
					}
				}

				for ($d = 0; $d < count($data)-1; $d++)
					for ($j = 0; $j < $everyNthPoint; $j++)
						$altitude[] = trim($data[$d]);
			}
		}

		return $altitude;
	}

	/**
	 * Get answer from GoogleMapsAPI for elevation as array
	 * @param array $CoordinatesAsStringArray
	 * @return mixed Array for success, otherwise false
	 */
	private function getElevationFromGoogleAsSimpleXml($CoordinatesAsStringArray) {
		$url    = 'http://maps.googleapis.com/maps/api/elevation/xml?locations='.implode('|', $CoordinatesAsStringArray).'&sensor=false';
		$String = Filesystem::getExternUrlContent($url);

		if (strlen($String) == 0) {
			Error::getInstance()->addError('Es konnten keine H&ouml;hendaten von Google empfangen werden.');
			return false;
		}

		$Xml = simplexml_load_string_utf8($String);

		if (!$Xml || !isset($Xml->status) || (string)$Xml->status != 'OK') {
			if (isset($Xml->status))
				Error::getInstance()->addError('Google-Service f&uuml;r H&ouml;hendaten liefert: '.((string)$Xml->status));
			else
				Filesystem::throwErrorForBadXml($String);
			return false;
		}

		return $Xml;
	}

	/**
	 * Calculate complete elevation
	 * @return int
	 */
	public function calculateElevation() {
		if (!$this->hasElevationData())
			return 0;

		$minimumElevation = (min($this->arrayForElevation) > 0) ? max($this->arrayForElevation) - min($this->arrayForElevation) : 0;
		$elevationArray   = $this->getElevationUpDownOfStep(true);

		return max($minimumElevation, $elevationArray[0], $elevationArray[1]);
	}

	/**
	 * Calculate difference in elevation
	 * @return int
	 */
	public function getElevationDifference() {
		if (!$this->hasElevationData())
			return 0;

		return end($this->arrayForElevation) - $this->arrayForElevation[0];
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