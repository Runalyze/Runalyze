<?php
/**
 * This file contains class::Darksky
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
use Runalyze\Profile\Weather\Mapping\DarkskyMapping;
use Runalyze\Profile\Weather\WeatherConditionProfile;

/**
 * Forecast-strategy for using darksky
 *
 * This weather forecast strategy uses the api of forecast.io
 * API documentation: https://darksky.net/dev/
 * To use this api, a location has to be set.
 *
 * The strategy uses <code>DARKSKY_API_KEY</code> if defined.
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Service\WeatherForecast\Strategy
 */
class Darksky implements StrategyInterface {
	/**
	 * URL for catching forecast
	 * @var string
	 */
	const URL = 'https://api.darksky.net/forecast/';

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
	    return (defined('DARKSKY_API_KEY') && strlen(DARKSKY_API_KEY))  ? true : false;
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
		return Sources::DARKSKY;
	}

	/**
	 * Load conditions for location
	 * @param \Runalyze\Data\Weather\Location $Location
	 */
	public function loadForecast(Location $Location) {
		$this->Result = array();
		$this->Location = $Location;

		if ($Location->hasPosition()) {
			$this->setFromURL( $Location->lat().','.$Location->lon().','.$Location->time() );
		}
		$this->updateLocation();
	}

	/**
	 * Set from url
	 * @param string $url
	 */
	public function setFromURL($url) {
		if (defined('DARKSKY_API_KEY') && strlen(DARKSKY_API_KEY))
			$url = self::URL.DARKSKY_API_KEY.'/'.$url;

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
		if (!isset($this->Result['currently']['icon'])) {
			return new Condition(WeatherConditionProfile::UNKNOWN);
		}

		return $this->translateCodeToCondition($this->Result['currently']['icon']);
	}

	/**
	 * Temperature
	 * @return \Runalyze\Data\Weather\Temperature
	 */
	public function temperature() {
		if (isset($this->Result['currently']) && isset($this->Result['currently']['temperature'])) {
			$value = round($this->Result['currently']['temperature']);
		} else {
			$value = null;
		}

		return new Temperature($value, Temperature::FAHRENHEIT);
	}

	/**
	 * WindSpeed
	 * @return \Runalyze\Data\Weather\WindSpeed
	 */
	public function windSpeed() {
		$WindSpeed = new WindSpeed();

		if (isset($this->Result['currently']) && isset($this->Result['currently']['windSpeed'])) {
			$WindSpeed->setMeterPerSecond($this->Result['currently']['windSpeed']);
		}

		return $WindSpeed;
	}

	/**
	 * WindDegree
	 * @return \Runalyze\Data\Weather\WindDegree
	 */
	public function windDegree() {
		if (isset($this->Result['currently']) && isset($this->Result['currently']['windBearing'])) {
			$value = round($this->Result['currently']['windBearing']);
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
		if (isset($this->Result['currently']) && isset($this->Result['currently']['humidity'])) {
			$value = $this->Result['currently']['humidity']*100;
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
		if (isset($this->Result['currently']) && isset($this->Result['currently']['pressure'])) {
			$value = round($this->Result['currently']['pressure']);
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
		if (isset($this->Result['latitude']) && isset($this->Result['longitude'])) {
			$this->Location->setPosition($this->Result['latitude'], $this->Result['longitude']);
		}
		if (isset($this->Result['dt']) && is_numeric($this->Result['dt'])) {
			$this->Location->setTimestamp($this->Result['dt']);
		}
	 }
	/**
	 * Translate api icon string to condition
	 *
	 * @see https://developer.forecast.io/docs/v2
	 * @param string $icon from forecast.io
	 * @return \Runalyze\Data\Weather\Condition
	 */
	private function translateCodeToCondition($icon) {
		return new Condition((new DarkskyMapping())->toInternal($icon));
	}
}
