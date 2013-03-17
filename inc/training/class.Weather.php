<?php
/**
 * This file contains class::Weather
 * @package Runalyze\Data
 */
/**
 * Weather
 * 
 * This class has a internal array with all weather types.
 * At the moment it's not possible for a user to change these.
 * 
 * In addition to the weather type (e.g. 'sunny'/'rainy') it's possible to set the temperature.
 * 
 * For loading current weather conditions, use <code>$Weather = new WeatherForecaster();</code>
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @package Runalyze\Data
 */
class Weather {
	/**
	* Array containing all rows from database
	* @var array
	*/
	static private $fullArray = array(
		1 => array('id' => 1, 'name' => 'unbekannt',	'img-class' => 'weather-1',	'order' => 0),
		2 => array('id' => 2, 'name' => 'sonnig',		'img-class' => 'weather-2',	'order' => 1),
		3 => array('id' => 3, 'name' => 'heiter',		'img-class' => 'weather-3',	'order' => 2),
		4 => array('id' => 4, 'name' => 'bew&ouml;lkt',	'img-class' => 'weather-4',	'order' => 3),
		5 => array('id' => 5, 'name' => 'wechselhaft',	'img-class' => 'weather-5',	'order' => 4),
		6 => array('id' => 6, 'name' => 'regnerisch',	'img-class' => 'weather-6',	'order' => 5),
		7 => array('id' => 7, 'name' => 'Schnee',		'img-class' => 'weather-7',	'order' => 6)
	);

	/**
	 * ID for unknown weather in database
	 * @var int
	 */
	static public $UNKNOWN_ID = 1;

	/**
	 * Internal ID
	 * @var int
	 */
	protected $id;

	/**
	 * Temperature in degree celsius, is optional
	 * @var int
	 */
	protected $temperature;

	/**
	 * Array from database
	 * @var array
	 */
	private $data;

	/**
	 * Constructor
	 * @param int $weather_id ID - Can be self::$FORECAST_ID to load forecast
	 * @param mixed $temperature
	 */
	public function __construct($weather_id, $temperature = null) {
		$this->id = $weather_id;
		$this->temperature = $temperature;

		$this->setData();
	}

	/**
	 * Set array from database as internal data-array
	 */
	private function setData() {
		$array = self::getFullArray();

		if (isset($array[$this->id]))
			$this->data = $array[$this->id];
	}

	/**
	 * Get data array for a given ID
	 * @param int $id
	 * @return array 
	 */
	static public function getDataFor($id) {
		if (isset(self::$fullArray[$id]))
			return self::$fullArray[$id];

		if ($id == self::$UNKNOWN_ID) {
			Error::getInstance()->addError('Fatal error - ID for unknown weather not set in internal array.');
			return array('id' => 0, 'name' => '?', 'img' => 'ka.png', 'order' => 0);
		}

		return self::getDataFor(self::$UNKNOWN_ID);
	}

	/**
	 * Get all rows from database
	 * @return array
	 */
	static public function getFullArray() {
		return self::$fullArray;
	}

	/**
	 * Get all rows except the one for unknown weather
	 * @return array
	 */
	static public function getArrayWithoutUnknown() {
		$array = self::getFullArray();
		unset($array[self::$UNKNOWN_ID]);

		return $array;
	}

	/**
	 * Get ID
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Get temperature
	 * @return int|null
	 */
	public function temperature() {
		return $this->temperature;
	}

	/**
	 * Returns the img-Tag for a weather-symbol
	 * @return string img-tag
	 */
	public function icon() {
		return Icon::getWeatherIcon($this->id);
	}

	/**
	 * Returns the name
	 * @return string name for this weather
	 */
	public function name() {
		return $this->data['name'];
	}

	/**
	 * Get as string with icon and temperature if set
	 * @return string
	 */
	public function asString() {
		$string = '';

		if (!$this->isUnknown())
			$string = $this->icon().' ';
		if (!is_null($this->temperature))
			$string .= $this->temperatureString();

		return $string;
	}

	/**
	 * Get string for temperature with unit
	 * @return string
	 */
	public function temperatureString() {
		return (is_null($this->temperature) ? '?' : $this->temperature).' &#176;C';
	}

	/**
	 * Get string with icon, name and temperature
	 * @return string
	 */
	public function fullString() {
		return $this->icon().' '.$this->name().' bei '.$this->temperatureString();
	}

	/**
	 * Is the weather-data empty?
	 * @return bool
	 */
	public function isEmpty() {
		return ($this->isUnknown() && is_null($this->temperature));
	}

	/**
	 * Is this the ID for unknown weather?
	 * @return bool
	 */
	public function isUnknown() {
		return ($this->id == self::$UNKNOWN_ID);
	}

	/**
	 * Transform condition name to ID
	 * @param string $condition
	 * @return int
	 */
	protected function conditionToId($condition) {
		if (empty($condition))
			return self::$UNKNOWN_ID;

		foreach (self::$fullArray as $id => $data)
			if ($data['name'] == $condition)
				return $id;
	}
}