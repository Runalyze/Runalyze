<?php
/**
 * This file contains class::WeatherOpenweathermap
 * @package Runalyze\Data\Weather
 */
/**
 * Forecast-strategy for using openweathermap.org
 * 
 * This weather forecast strategy uses the api of openweathermap.org
 * To use this api, a location has to be set.
 * CONF_PLZ is currently used as harcoded location.
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class WeatherOpenweathermap implements WeatherForecastStrategy {
	/**
	 * URL for catching forecast
	 * @var string
	 */
	static private $URL = 'http://api.openweathermap.org/data/2.1/find/name';

	/**
	 * Result from json
	 * @var array
	 */
	protected $Result = array();

	/**
	 * Is a location set?
	 * @todo Don't hardcode CONF_PLZ!
	 * @return bool
	 */
	protected function hasLocation() {
		return (strlen(CONF_PLZ) > 0);
	}

	/**
	 * Get location
	 * @todo Don't hardcode CONF_PLZ!
	 * @return string
	 */
	protected function getLocation() {
		return CONF_PLZ;
	}

	/**
	 * Load conditions
	 */
	public function loadForecast() {
		if ($this->hasLocation()) {
			$this->tryToCatchJSON();
		}
	}

	/**
	 * Try to load result
	 */
	protected function tryToCatchJSON() {
		$this->setFromJSON( Filesystem::getExternUrlContent(self::$URL.'?q='.$this->getLocation().'&units=metric') );
	}

	/**
	 * Set result from json
	 * @param string $JSON
	 */
	public function setFromJSON($JSON) {
		if ($JSON) {
			$this->Result = json_decode($JSON, true);
			$this->Result = $this->Result['list'][0];
		}
	}

	/**
	 * Get weather string
	 * @return string
	 */
	public function getConditionAsString() {
		if (!isset($this->Result['weather']))
			return '';

		return $this->translateCodeToInternalName($this->Result['weather'][0]['id']);
	}

	/**
	 * Get temperature
	 * @return mixed
	 */
	public function getTemperature() {
		if (isset($this->Result['main']) && isset($this->Result['main']['temp']))
			return round($this->Result['main']['temp']);

		return null;
	}

	/**
	 * Translate api code to internal name
	 * 
	 * @see http://openweathermap.org/wiki/API/Weather_Condition_Codes
	 * @param int $code Code from openweathermap.org
	 * @return string
	 */
	private function translateCodeToInternalName($code) {
		switch($code) {
			case 800:
				return 'sunny';
			case 801:
				return 'fair';
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
				return 'changeable';
			case 803:
			case 804:
				return 'cloudy';
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
				return 'rainy';
			case 600:
			case 601:
			case 602:
			case 611:
			case 621:
				return 'snow';
			default:
				return 'unknown';
		}
	}
}