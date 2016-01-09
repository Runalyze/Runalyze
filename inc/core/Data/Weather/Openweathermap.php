<?php
/**
 * This file contains class::WeatherOpenweathermap
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

use Cache;

/**
 * Forecast-strategy for using openweathermap.org
 * 
 * This weather forecast strategy uses the api of openweathermap.org
 * To use this api, a location has to be set.
 * 
 * The strategy uses <code>OPENWEATHERMAP_API_KEY</code> if defined.
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class Openweathermap implements ForecastStrategyInterface {
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
	 * @var string
	 */
	const CACHE_PREFIX = 'weather.';

	/**
	 * Result from json
	 * @var array
	 */
	protected $Result = array();

	/**
	 * Load conditions for location
	 * @param Location $Location
	 */
	public function loadForecast(Location $Location) {
		$this->Result = array();

		if ($Location->isOld() && $Location->hasLocationName()) {
			$this->setFromURL( self::URL_HISTORY.'/city?q='.$Location->name().'&start='.$Location->time().'&cnt=1' );
		}

		if (empty($this->Result)) {
			if ($Location->hasPosition()) {
				$this->setFromURL( self::URL.'?lat='.$Location->lat().'&lon='.$Location->lon() );
			} elseif ($Location->hasLocationName()) {
				$this->setFromURL( self::URL.'?q='.$Location->name(), $this->cacheKey($Location->name()) );
			}
		}
	}

	/**
	 * Generate cache key
	 * @param string $locationName
	 * @return string
	 */
	protected function cacheKey($locationName) {
		return self::CACHE_PREFIX.urlencode($locationName);
	}

	/**
	 * Set from url
	 * @param string $url
	 * @param string $cacheKey [optional] if true result will be cached
	 */
	public function setFromURL($url, $cacheKey = false) {
		if ($cacheKey !== false) {
			$this->Result = Cache::get($cacheKey, 1);

			if ($this->Result != null) {
				return;
			} else {
				$this->Result = array();
			}
		}

		if (defined('OPENWEATHERMAP_API_KEY') && strlen(OPENWEATHERMAP_API_KEY))
			$url .= '&APPID='.OPENWEATHERMAP_API_KEY;

		$this->setFromJSON( \Filesystem::getExternUrlContent($url) );

		if ($cacheKey !== false) {
			Cache::set($cacheKey, $this->Result, 7200, 1);
		}
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
			$value = $this->Result['main']['temp'];
		} else {
			$value = null;
		}

		return new Temperature($value, Temperature::KELVIN);
	}

	/**
	 * Translate api code to condition
	 * 
	 * @see http://openweathermap.org/wiki/API/Weather_Condition_Codes
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