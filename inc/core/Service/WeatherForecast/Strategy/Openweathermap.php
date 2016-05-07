<?php
/**
 * This file contains class::Openweathermap
 * @package Runalyze\Service\WeatherForecast\Strategy
 */

namespace Runalyze\Service\WeatherForecast\Strategy;

use Runalyze\Data\Weather\Temperature;
use Runalyze\Data\Weather\Humidity;
use Runalyze\Data\Weather\Pressure;
use Runalyze\Data\Weather\WindSpeed;
use Runalyze\Data\Weather\WindDegree;
use Runalyze\Data\Weather\Condition;
use Runalyze\Data\Weather\Sources;
use Runalyze\Data\Weather\Location;

/**
 * Forecast-strategy for using openweathermap.org
 * 
 * This weather forecast strategy uses the api of openweathermap.org
 * To use this api, a location has to be set.
 * 
 * The strategy uses <code>OPENWEATHERMAP_API_KEY</code> if defined.
 *
 * @author Hannes Christiansen
 * @package Runalyze\Service\WeatherForecast\Strategy
 */
class Openweathermap implements StrategyInterface {
	/**
	 * URL for catching forecast
	 * @var string
	 */
	const URL = 'http://api.openweathermap.org/data/2.5/weather';

	/**
	 * URL for catching forecast
	 * @var string
	 */
	const URL_HISTORY = 'http://api.openweathermap.org/data/2.5/history';

	/**
	 * Location
	 * @var \Runalyze\Data\Weather\Location $Location
	 */
	protected $Location = null;
	
	/**
	 * Result from json
	 * @var array
	 */
	protected $Result = array();

	/*
	 * @return boolean
	 */
	public function isPossible() {
	    return (defined('OPENWEATHERMAP_API_KEY') && strlen(OPENWEATHERMAP_API_KEY))  ? true : false;
	}
	
	/**
	 * Should this data be cached?
	 * @return boolean
	 */
	public function isCachable() {
	    return true;
	}

	/**
	 * @return boolean
	 */
	public function wasSuccessfull() {
		return !empty($this->Result);
	}

	/**
	 * @return int
	 */
	public function sourceId()
	{
		return Sources::OPENWEATHERMAP;
	}

	/**
	 * Load conditions for location
	 * @param \Runalyze\Data\Weather\Location $Location
	 */
	public function loadForecast(Location $Location) {
		$this->Result = array();
		$this->Location = $Location;
		
		if ($Location->isOlderThan(7200) && $Location->hasLocationName()) {
			// Historical data needs a paid account (150$/month)
			// @see http://openweathermap.org/price
			//$this->setFromURL( self::URL_HISTORY.'/city?q='.$Location->name().'&start='.$Location->time().'&cnt=1' );
		} elseif (empty($this->Result)) {
			if ($Location->hasPosition()) {
				$this->setFromURL( self::URL.'?lat='.$Location->lat().'&lon='.$Location->lon() );
			} elseif ($Location->hasLocationName()) {
				$this->setFromURL( self::URL.'?q='.$Location->name() );
			}
		}

		$this->updateLocation();
	}

	/**
	 * Set from url
	 * @param string $url
	 */
	public function setFromURL($url) {
		if (defined('OPENWEATHERMAP_API_KEY') && strlen(OPENWEATHERMAP_API_KEY))
			$url .= '&APPID='.OPENWEATHERMAP_API_KEY;

		$this->setFromJSON( \Filesystem::getExternUrlContent($url) );
	}

	/**
	 * Set result from json
	 * @param string $JSON
	 */
	public function setFromJSON($JSON) {
		if ($JSON) {
			$this->Result = json_decode($JSON, true);

			if (isset($this->Result['list'])) {
				if (!empty($this->Result['list'])) {
					$this->Result = $this->Result['list'][0];
				} else {
					$this->Result = array();
				}
			}
		}
	}

	/**
	 * Condition
	 * @return \Runalyze\Data\Weather\Condition
	 */
	public function condition() {
		if (!isset($this->Result['weather'])) {
			return new Condition(Condition::UNKNOWN);
		}

		return $this->translateCodeToCondition($this->Result['weather'][0]['id']);
	}

	/**
	 * Temperature
	 * @return \Runalyze\Data\Weather\Temperature
	 */
	public function temperature() {
		if (isset($this->Result['main']) && isset($this->Result['main']['temp'])) {
			$value = round($this->Result['main']['temp']);
		} else {
			$value = null;
		}

		return new Temperature($value, Temperature::KELVIN);
	}
	
	/**
	 * WindSpeed
	 * @return \Runalyze\Data\Weather\WindSpeed
	 */
	public function windSpeed() {
		$WindSpeed = new WindSpeed();

		if (isset($this->Result['wind']) && isset($this->Result['wind']['speed'])) {
			$WindSpeed->setMeterPerSecond($this->Result['wind']['speed']);
		}
		
		return $WindSpeed;
	}
	
	/**
	 * WindDegree
	 * @return \Runalyze\Data\Weather\WindDegree
	 */
	public function windDegree() {
		if (isset($this->Result['wind']) && isset($this->Result['wind']['deg'])) {
			$value = round($this->Result['wind']['deg']);
		} else {
			$value = null;
		}

		return new WindDegree($value);
	}
	
	/**
	 * Humidity
	 * @return \Runalyze\Data\Weather\Humidity
	 */
	public function humidity() {
		if (isset($this->Result['main']) && isset($this->Result['main']['humidity'])) {
			$value = round($this->Result['main']['humidity']);
		} else {
			$value = null;
		}

		return new Humidity($value);
	}
	
	/**
	 * Pressure
	 * @return \Runalyze\Data\Weather\Pressure
	 */
	public function pressure() {
		if (isset($this->Result['main']) && isset($this->Result['main']['pressure'])) {
			$value = round($this->Result['main']['pressure']);
		} else {
			$value = null;
		}

		return new Pressure($value);
	}
	
	/**
	 * Location object
	 * @return \Runalyze\Data\Weather\Location
	 */
	public function location() {
	    return $this->Location;
	}
	
	/**
	 * Update Location Object
	 */
	 protected function updateLocation() {
		if (isset($this->Result['coord']) && isset($this->Result['coord']['lon']) && isset($this->Result['coord']['lat'])) {
			$this->Location->setPosition($this->Result['coord']['lat'], $this->Result['coord']['lon']);
		}
		if (isset($this->Result['dt']) && is_numeric($this->Result['dt'])) {
			$this->Location->setTimestamp($this->Result['dt']);
		}
	 }
	/**
	 * Translate api code to condition
	 * 
	 * @see http://openweathermap.org/weather-conditions
	 * @param int $code Code from openweathermap.org
	 * @return \Runalyze\Data\Weather\Condition
	 */
	private function translateCodeToCondition($code) {
		switch($code) {
			case 800:
				return new Condition(Condition::SUNNY);
			case 801:
				return new Condition(Condition::FAIR);
			case 200:
			case 210:
			case 211:
			case 212:
			case 221:
			case 230:
			case 231: 
			case 232:
			    return new Condition(Condition::THUNDERSTORM);
			case 300:
			case 301:
			case 802:
			case 701:
			case 711:
			case 721:
			case 731:
			case 741:
				return new Condition(Condition::CHANGEABLE);
			case 803:
			case 804:
				return new Condition(Condition::CLOUDY);
			case 502:
			case 503:
			case 504:
			case 521:
			case 522:
			case 531:
			    return new Condition(Condition::HEAVYRAIN);
			case 500:
			case 501:			    
			case 511:
			case 520:
			case 302:
			case 310:
			case 311:
			case 312:
			case 321:
			case 201:
			case 202:
				return new Condition(Condition::RAINY);
			case 600:
			case 601:
			case 602:
			case 611:
			case 621:
				return new Condition(Condition::SNOWING);
			default:
				return new Condition(Condition::UNKNOWN);
		}
	}
}