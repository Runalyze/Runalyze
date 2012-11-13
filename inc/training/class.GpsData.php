<?php
/**
 * Class: GpsData
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class GpsData {
	/**
	 * Minimal difference per step to be recognized for elevation data
	 * @var int
	 */
	public static $minElevationDiff = 3;

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
	public function __construct($TrainingData) {
		$this->arrayForTime      = $this->loadArrayDataFrom($TrainingData['arr_time']);
		$this->arrayForLatitude  = $this->loadArrayDataFrom($TrainingData['arr_lat']);
		$this->arrayForLongitude = $this->loadArrayDataFrom($TrainingData['arr_lon']);
		$this->arrayForElevation = $this->loadArrayDataFrom($TrainingData['arr_alt']);
		$this->arrayForDistance  = $this->loadArrayDataFrom($TrainingData['arr_dist']);
		$this->arrayForHeartrate = $this->loadArrayDataFrom($TrainingData['arr_heart']);
		$this->arrayForPace      = $this->loadArrayDataFrom($TrainingData['arr_pace']);
		$this->arraySizes        = max(count($this->arrayForTime), count($this->arrayForLatitude));

		if (isset($TrainingData['gps_cache_object']))
			$this->initCache($TrainingData['id'], $TrainingData['gps_cache_object']);
		else
			$this->initCache(0, false);
	}

	/**
	 * Load array for internal data from string
	 * @param string $string
	 * @return array
	 */
	private function loadArrayDataFrom($string) {
		$array = explode(Training::$ARR_SEP, $string);

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
		return !empty($this->arrayForLatitude) && (count($this->arrayForLongitude) > 1) && max($this->arrayForLatitude) > 0;
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
	protected function getElevationUpDownOfStep($complete = false) {
		if (empty($this->arrayForElevation) || (!$complete && !isset($this->arrayForElevation[$this->arrayIndex])))
			return array(0, 0);

		$positiveElevation = 0;
		$negativeElevation = 0;
		$stepArray = $complete ? $this->arrayForElevation : array_slice($this->arrayForElevation, $this->arrayLastIndex, ($this->arrayIndex - $this->arrayLastIndex));

		foreach ($stepArray as $i => $step) {
			if ($i != 0 && $stepArray[$i] != 0 && $stepArray[$i-1] != 0) {
				$elevationDifference = $stepArray[$i] - $stepArray[$i-1];
				$positiveElevation += ($elevationDifference > self::$minElevationDiff) ? $elevationDifference : 0;
				$negativeElevation -= ($elevationDifference < -1*self::$minElevationDiff) ? $elevationDifference : 0;
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
		if (!$this->Cache->isEmpty() && $distance = 1)
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
		while ($this->nextKilometer(0.1)) {
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
		while ($this->nextKilometer(0.1)) {
			if ($this->plotUsesTimeOnXAxis())
				$index = (string)($this->getTime()).'000';
			else
				$index = (string)($this->getDistance());

			$Heartrate = $this->getCurrentPlotDataFor('heartrate');
			$Data['pace'][$index]                = $this->getCurrentPlotDataFor('pace');
			$Data['elevation'][$index]           = $this->getCurrentPlotDataFor('elevation');
			$Data['heartrate'][$index]           = $Heartrate;
			$Data['heartrate100'][$index]        = 100*$Heartrate/HF_MAX;
			$Data['heartrate100reserve'][$index] = Running::PulseInPercentReserve($Heartrate);
		}

		return $Data;
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
	 * Correct the elevation data and return new array
	 * @return array
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

		$this->correctInvalidElevationValues();

		return $this->arrayForElevation;
	}

	/**
	 * Correct invalid values for elevation in case of missing latitude/longitude
	 */
	private function correctInvalidElevationValues() {
		$this->startLoop();

		while ($this->nextStep()) {
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