<?php
/**
 * This file contains the class::GpsData for handling GPS-data of a training
 */
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
		return ($this->arrayIndex == $this->arraySizes-1);
	}

	/**
	 * Go to next step if possible
	 * @return bool
	 */
	public function nextStep() {
		$this->arrayLastIndex = $this->arrayIndex;

		if ($this->loopIsAtEnd())
			return false;

		$this->arrayIndex++;

		return true;
	}

	/**
	 * Go to next kilometer if possible
	 * @return bool
	 */
	public function nextKilometer() {
		$this->arrayLastIndex = $this->arrayIndex;

		if ($this->loopIsAtEnd())
			return false;

		while ($this->currentKilometer() == floor($this->arrayForDistance[$this->arrayLastIndex]))
			$this->arrayIndex++;

		return true;
	}

	/**
	 * Get the current kilometer
	 * @return float
	 */
	public function currentKilometer() {
		if ($this->loopIsAtDefaultIndex())
			return 0;

		if ($this->loopIsAtEnd())
			return end($this->arrayForDistance);

		return floor($this->arrayForDistance[$this->arrayIndex]);
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
				$positiveElevation += ($elevationDifference > Training::$minElevationDiff) ? $elevationDifference : 0;
				$negativeElevation -= ($elevationDifference < -1*Training::$minElevationDiff) ? $elevationDifference : 0;
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
			$zone = round(100 * $this->getHeartrate() / Helper::getHFmax() / 10);
		
			if (!isset($Zones[$zone]))
				$Zones[$zone] = array('time' => 0, 'distance' => 0, 'pace-sum' => 0, 'num' => 0);
		
			$Zones[$zone]['time']     += $this->getTimeOfStep();
			$Zones[$zone]['distance'] += $this->getDistanceOfStep();
			$Zones[$zone]['pace-sum'] += $this->getPace();
			$Zones[$zone]['num']++;
		}
		
		krsort($Zones);

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
			$zone = round($this->getPace() / 60);

			if (!isset($Zones[$zone]))
				$Zones[$zone] = array('time' => 0, 'distance' => 0, 'hf-sum' => 0, 'num' => 0);

			$Zones[$zone]['time']     += $this->getTimeOfStep();
			$Zones[$zone]['distance'] += $this->getDistanceOfStep();
			$Zones[$zone]['hf-sum']   += $this->getHeartrate();
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
	 * Correct the elevation data
	 */
	public function getElevationCorrection() {
		// TODO
	}

	/**
	 * Calculate complete elevation
	 */
	public function calculateElevation() {
		// TODO
	}

	/**
	 * Compress data to a minimum
	 */
	public function compressData() {
		// TODO
	}
}
?>