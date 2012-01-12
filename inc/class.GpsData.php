<?php
/**
 * This file contains the class::GpsData for handling GPS-data of a training
 */

Config::register('Eingabeformular', 'TRAINING_ELEVATION_SERVER', 'select',
	array('google' => true, 'geonames' => false), 'Server f&uuml;r H&ouml;henkorrektur',
	array('maps.googleapis.com', 'ws.geonames.org'));

/**
 * Class: GpsData
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
 */
class GpsData {
	/**
	 * Minimal difference per step to be recognized for elevation data
	 * @var int
	 */
	public static $minElevationDiff = 2;

	/**
	 * Only every n-th point will be taken for the elevation
	 * @var int
	 */
	public static $everyNthElevationPoint = 5;

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
		$this->arraySizes        = count($this->arrayForTime);
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
		$this->stepSize   = (int)$size;
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

		return true;
	}

	/**
	 * Go to next kilometer if possible
	 * @param double $distance
	 * @return bool
	 */
	public function nextKilometer($distance = 1) {
		$this->arrayLastIndex = $this->arrayIndex;

		if ($this->loopIsAtEnd())
			return false;

		while ($this->currentKilometer($distance) == floor($this->arrayForDistance[$this->arrayLastIndex]/$distance)*$distance)
			$this->arrayIndex++;

		return true;
	}

	/**
	 * Get the current kilometer
	 * @param double $distance
	 * @return float
	 */
	public function currentKilometer($distance = 1) {
		if ($this->loopIsAtDefaultIndex())
			return 0;

		if ($this->loopIsAtEnd())
			return end($this->arrayForDistance);

		return floor($this->arrayForDistance[$this->arrayIndex]/$distance)*$distance;
	}

	/**
	 * Are information for pace available?
	 */
	public function hasPaceData() {
		return !empty($this->arrayForPace);
	}

	/**
	 * Are information for elevation available?
	 */
	public function hasElevationData() {
		return !empty($this->arrayForElevation);
	}

	/**
	 * Are information for heartrate available?
	 */
	public function hasHeartrateData() {
		return !empty($this->arrayForHeartrate) && (max($this->arrayForHeartrate) > 60);
	}

	/**
	 * Are information for latitude/longitude available?
	 */
	public function hasPositionData() {
		return !empty($this->arrayForLatitude) && (count($this->arrayForLongitude) > 1);
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
	 * @return array
	 */
	protected function getElevationUpDownOfStep() {
		if (empty($this->arrayForElevation) || !isset($this->arrayForElevation[$this->arrayIndex]))
			return array(0, 0);

		$positiveElevation = 0;
		$negativeElevation = 0;
		$stepArray = array_slice($this->arrayForElevation, $this->arrayLastIndex, ($this->arrayIndex - $this->arrayLastIndex));

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

		$Zones = array();
		$this->startLoop();

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

		$Zones = array();
		$this->startLoop();

		while ($this->nextStep()) {
			$zone = floor($this->getAveragePaceOfStep() / 60);

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
	 * @return array
	 */
	public function getRoundsAsFilledArray() {
		$rounds = array();
		
		$this->startLoop();
		while ($this->nextKilometer()) {
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
	 * Get plot data for a given key
	 * @param unknown_type $key
	 */
	protected function getPlotDataFor($key) {
		$Data = array();

		$this->startLoop();
		while ($this->nextKilometer(0.1)) {
			switch ($key) {
				case "elevation":
					$value = $this->getAverageElevationOfStep();
					break;
				case "heartrate100":
					$value = 100*$this->getAverageHeartrateOfStep()/HF_MAX;
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

			$km = (string)($this->getDistance());
			$Data[$km] = $value;
		}

		return $Data;
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
	public function getPlotDataForHeartrate() {
		return $this->getPlotDataFor('heartrate');
	}

	/**
	 * Get array as plot-data for heartrate in percent
	 */
	public function getPlotDataForHeartrateInPercent() {
		return $this->getPlotDataFor('heartrate100');
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
				$this->arrayForElevation = $this->getElevationCorrectionFromGoogle();
				break;
			case 'geonames':
			default:
				$this->arrayForElevation = $this->getElevationCorrectionFromGeonames();
		}

		return $this->arrayForElevation;
	}

	/**
	 * Get elevation correction from GoogleMapsAPI
	 * @return array
	 */
	public function getElevationCorrectionFromGoogle() {
		$numForEachCall = 20;
		$altitude       = array();
		$string         = array();

		for ($i = 0; $i < $this->arraySizes; $i++) {
			if ($i%Training::$everyNthElevationPoint == 0)
				$string[] = $this->arrayForLatitude[$i].','.$this->arrayForLongitude[$i];

			if (($i+1)%($numForEachCall*Training::$everyNthElevationPoint) == 0 || $i == $this->arraySizes-1) {
				$Xml = $this->getElevationFromGoogleAsSimpleXml($string);

				if ($Xml === false)
					return false;

				foreach ($Xml->xpath('result') as $Point) {
					for ($p = 0; $p < self::$everyNthElevationPoint; $p++)
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
		$numForEachCall = 20;
		$altitude = array();
		$lats     = array();
		$longs    = array();

		for ($i = 0; $i < $this->arraySizes; $i++) {
			if ($i%Training::$everyNthElevationPoint == 0) {
				$lats[]   = $this->arrayForLatitude[$i];
				$longs[]  = $this->arrayForLongitude[$i];
			}

			if (($i+1)%($numForEachCall*Training::$everyNthElevationPoint) == 0 || $i == $this->arraySizes-1) {
				$html = false;

				while ($html === false) {
					$html = @file_get_contents('http://ws.geonames.org/srtm3?lats='.implode(',', $lats).'&lngs='.implode(',', $longs));
					if (substr($html,0,1) == '<')
						$html = false;
					else {
						$data = explode("\r\n", $html);
						$lats = array();
						$longs = array();
					}
				}

				for ($d = 0; $d < count($data)-1; $d++)
					for ($j = 0; $j < self::$everyNthElevationPoint; $j++)
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
		$url = 'http://maps.googleapis.com/maps/api/elevation/xml?locations='.implode('|', $CoordinatesAsStringArray).'&sensor=false';
		$Xml = simplexml_load_file_utf8($url);

		if (!isset($Xml->status) || (string)$Xml->status != 'OK') {
			Error::getInstance()->addError('GoogleMapsAPI returned bad xml');
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

		$elevationPoints 	= $this->arrayForElevation;
		$minimumElevation   = (min($elevationPoints) > 0) ? max($elevationPoints) - min($elevationPoints) : 0;
		$positiveElevation 	= 0;  $up   = false;
		$negativeElevation 	= 0;  $down = false;
		$currentElevation   = 0;

		// Algorithm: must be at least 5m up/down without down/up
		foreach ($elevationPoints as $i => $p) {
			if ($i != 0 && $elevationPoints[$i] != 0 && $elevationPoints[$i-1] != 0) {
				$diff = $p - $elevationPoints[$i-1];
				if ( ($diff > 0 && !$down) || ($diff < 0 && !$up) )
					$currentElevation += $diff;
				else {
					if ($up && abs($currentElevation) >= 5)
						$positiveElevation += $currentElevation;
					elseif ($down && abs($currentElevation) >= 5)
						$negativeElevation -= $currentElevation;
					$currentElevation = $diff;
				}
				$up   = ($diff > 0);
				$down = ($diff < 0);
			}
		}

		return max($minimumElevation, $positiveElevation, $negativeElevation);
	}

	/**
	 * Compress data to a minimum
	 */
	public function compressData() {
		// TODO
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
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;
	
		return ($miles * 1.609344);
	}
}
?>