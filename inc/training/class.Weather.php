<?php
/**
 * This file contains the class::Weather for handling weather-types
 */
/**
 * Class: Weather
 * @author Hannes Christiansen <mail@laufhannes.de>
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
	 * ID for loading weather from API
	 * @var int
	 */
	static public $FORECAST_ID = -1;

	/**
	 * Internal ID
	 * @var int
	 */
	private $id;

	/**
	 * Temperature in degree celsius, is optional
	 * @var int
	 */
	private $temperature;

	/**
	 * Array from database
	 * @var array
	 */
	private $data;

	/**
	* Language used
	* @var string
	*/
	private $lang = 'en';

	/**
	 * Constructor
	 */
	public function __construct($weather_id, $temperature = null) {
		$this->id = $weather_id;
		$this->temperature = $temperature;

		if ($this->isForecast())
			$this->loadForecast();
		else
			$this->setData();
	}

	/**
	 * Destructor
	 */
	public function __destruct() {}

	/**
	 * Get object for forecast
	 * @return Weather
	 */
	static public function Forecaster() {
		return new Weather(self::$FORECAST_ID);
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
	 * Get select-box for all weather-ids
	 * @param mixed $selected [optional] Value to be selected
	 * @return string
	 */
	static public function getSelectBox($selected = -1) {
		if ($selected == -1 && isset($_POST['weatherid']))
			$selected = $_POST['weatherid'];

		$weather = self::getFullArray();
		foreach ($weather as $id => $data)
			$weather[$id] = $data['name'];

		return HTML::selectBox('weatherid', $weather, $selected);
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
	 * Boolean flag: Is this object a forecast?
	 * @return bool
	 */
	private function isForecast() {
		return ($this->id == self::$FORECAST_ID);
	}

	/**
	 * Is this the ID for unknown weather?
	 * @return bool
	 */
	public function isUnknown() {
		return ($this->id == self::$UNKNOWN_ID);
	}

	/**
	 * Set internal data to post-array if not set
	 */
	public function setPostDataIfEmpty() {
		if (!isset($_POST['weatherid']))
			$_POST['weatherid'] = $this->id;
		if (!isset($_POST['temperature']))
			$_POST['temperature'] = $this->temperature;
	}

	/**
	 * Set default data for internal id/temperature
	 */
	private function setDefaultVars() {
		$this->id = self::$UNKNOWN_ID;
		$this->temperature = NULL;

		$this->setData();
	}

	/**
	 * Load current conditions from API and set as internal data
	 */
	private function loadForecast() {
		if (CONF_PLZ > 0) {
			$JsonAp = Filesystem::getExternUrlContent('http://api.openweathermap.org/data/2.1/find/name?q='.CONF_PLZ.'&units=metric');
			if ($JsonAp) {
				$WeatherInfo = json_decode($JsonAp, true);
				$Temperature = $WeatherInfo['list'][0]['main']['temp'];
				$WeatherID = $WeatherInfo['list'][0]['weather'][0]['id'];
				if (!is_null($Temperature) && !is_null($WeatherID)) {
					$this->temperature = $WeatherInfo['list'][0]['main']['temp'];
					$transid = self::translateOpenWeatherConditionToInternalName($WeatherID);
					foreach (self::$fullArray as $id => $data)
					if ($data['name'] == $transid) 
						$this->id = $id;
						return;
					} else {
						Error::getInstance()->addNotice('Die Wetterdaten konnten nicht geladen werden.');
					}
			}
		}	
	}

	/**
	 * Translate google string for condition to database-string (Database: http://openweathermap.org/wiki/API/Weather_Condition_Codes)
	 * @param int $id
	 * @return string
	 */
	private static function translateOpenWeatherConditionToInternalName($id) {
		switch($id) {
			case 800:
				return 'sonnig';
			case 801:
				return 'heiter';
			case 200:
			case 210:
			case 211:
			case 212:
			case 221:
			case 230:
			case 231: 
			case 232:
			case 300:
			case 301:
			case 802:
			case 701:
			case 711:
			case 721:
			case 731:
			case 741:
				return 'wechselhaft';
			case 803:
			case 804:
				return 'bew&ouml;lkt';
			case 500:
			case 501:
			case 502:
			case 503:
			case 504:
			case 511:
			case 520:
			case 521:
			case 522:
			case 300:
			case 301:
			case 302:
			case 310:
			case 311:
			case 312:
			case 321:
			case 201:
			case 202:
				return 'regnerisch';
			case 600:
			case 601:
			case 602:
			case 611:
			case 621:
				return 'Schnee';
			default:
				return 'unbekannt';
		}
	}
}