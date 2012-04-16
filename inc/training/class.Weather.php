<?php
/**
 * This file contains the class::Weather for handling weather-types
 */
/**
 * Class: Weather
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
 */
class Weather {
	/**
	* Array containing all rows from database
	* @var array
	*/
	static private $fullArray = array(
		1 => array('id' => 1, 'name' => 'unbekannt',	'img' => 'ka.png',			'order' => 0),
		2 => array('id' => 2, 'name' => 'sonnig',		'img' => 'sonnig.png',		'order' => 1),
		3 => array('id' => 3, 'name' => 'heiter',		'img' => 'heiter.png',		'order' => 2),
		4 => array('id' => 4, 'name' => 'bew&ouml;lkt',	'img' => 'bewoelkt.png',	'order' => 3),
		5 => array('id' => 5, 'name' => 'wechselhaft',	'img' => 'wechselhaft.png',	'order' => 4),
		6 => array('id' => 6, 'name' => 'regnerisch',	'img' => 'regnerisch.png',	'order' => 5),
		7 => array('id' => 7, 'name' => 'Schnee',		'img' => 'Schnee.png',		'order' => 6)
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

		return getDataFor(self::$UNKNOWN_ID);
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
			$Xml = Filesystem::getExternUrlContent('http://www.google.de/ig/api?weather='.CONF_PLZ.'&hl='.$this->lang);

			if (strlen($Xml) > 1) {
				$Xml         = simplexml_load_string_utf8($Xml);
				$Temperature = $this->getTemperatureFromXML($Xml);
				$WeatherID   = $this->getWeatherIdFromXML($Xml);

				if (!is_null($Temperature) && !is_null($WeatherID)) {
					$this->temperature = (int)$Temperature;
					$this->id          = $WeatherID;
					return;
				}
			} else {
				Error::getInstance()->addNotice('Die Wetterdaten konnten nicht geladen werden.');
			}
		}

		$this->setDefaultVars();
	}

	/**
	 * Get current temperature (in celsius) from Xml
	 * @param object $Xml
	 * @return mixed
	 */
	private function getTemperatureFromXML(&$Xml) {
		$temp = $Xml->xpath('//current_conditions/temp_c');

		if (is_array($temp) && isset($temp[0]['data']))
			return (string)$temp[0]['data'];

		return NULL;
	}

	/**
	 * Get current temperature (in celsius) from Xml
	 * @param object $Xml
	 * @return mixed
	 */
	private function getWeatherIdFromXML(&$Xml) {
		$temp = $Xml->xpath('//current_conditions/condition');

		if (is_array($temp) && isset($temp[0]['data']))
			return $this->getIdFromAPICondition((string)$temp[0]['data']);

		return NULL;
	}

	/**
	 * Translate condition-data from API to internal ID
	 * @param string $condition
	 * @return int
	 */
	private function getIdFromAPICondition($condition) {
		$condition = $this->translateGoogleConditionToInternalName($condition);
		foreach (self::$fullArray as $id => $data)
			if ($data['name'] == $condition)
				return $id;

		return self::$UNKNOWN_ID;
	}

	/**
	 * Translate google string for condition to database-string
	 * @param string $string
	 * @return string
	 */
	private function translateGoogleConditionToInternalName($string) {
		if ($this->lang == 'de')
			switch ($string) {
				case 'Meist sonnig':
				case 'Klar':
					return 'sonnig';
				case 'Teils sonnig':
					return 'heiter';
				case 'Bedeckt':
				case 'Meistens bewölkt':
				case 'Bewölkt':
				case 'Nebel':
					return 'bew&ouml;lkt';
				case 'Vereinzelt stürmisch':
				case 'Vereinzelte Schauer':
				case 'Vereinzelt Regen':
				case 'Leichter Regen':
				case 'Nieselregen':
				case 'Dunst':
					return 'wechselhaft';
				case 'Regen':
				case 'Starker Regen':
				case 'Gewitterschauer':
					return 'regnerisch';
				case 'Leichter Schneefall':
				case 'Starker Schneefall':
				case 'Schnee':
					return 'Schnee';
				default:
					Error::getInstance()->addNotice('Unknown condition from GoogleWeatherAPI: "'.$string.'"');
					return 'unbekannt';
			}
		else
			switch ($string) {
				case 'Mostly Sunny':
				case 'Sunny':
				case 'Clear':
					return 'sonnig';
				case 'Partly Sunny':
				case 'Partly Cloudy':
					return 'heiter';
				case 'Overcast':
				case 'Mostly Cloudy':
				case 'Cloudy':
				case 'Fog':
					return 'bew&ouml;lkt';
				case 'Mist':
				case 'Storm':
				case 'Chance of rain':
				case 'Scattered showers':
				case 'Scattered thunderstorms':
				case 'Windy':
					return 'wechselhaft';
				case 'Rain':
				case 'Light rain':
				case 'Showers':
				case 'Rain and snow':
				case 'Freezing drizzle':
				case 'Chance of tstorm':
				case 'Thunderstorm':
				case 'Sleet':
					return 'regnerisch';
				case 'Haze':
				case 'Flurries':
				case 'Icy':
				case 'Snow':
				case 'Light snow':
				case 'Chance of snow':
				case 'Scattered snow showers':
					return 'Schnee';
				default:
					Error::getInstance()->addNotice('Unknown condition from GoogleWeatherAPI: "'.$string.'"');
					return 'unbekannt';
			}
	}
}