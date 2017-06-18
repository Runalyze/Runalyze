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
use Runalyze\Profile\Weather\Mapping\OpenWeatherMapMapping;
use Runalyze\Profile\Weather\WeatherConditionProfile;

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

		if (!$Location->isOlderThan(7200)) {
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
			return new Condition(WeatherConditionProfile::UNKNOWN);
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
			$this->Location->setDateTime((new \DateTime())->setTimestamp($this->Result['dt']));
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
		return new Condition((new OpenWeatherMapMapping())->toInternal($code));
	}
}
