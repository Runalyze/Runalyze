<?php
/**
 * This file contains class::DBWeatherCache
 * @package Runalyze\Service\WeatherForecast\Strategy
 */

namespace Runalyze\Service\WeatherForecast\Strategy;

use Runalyze\Model\WeatherCache;
use Runalyze\Data\Weather;
use Runalyze\Service\WeatherForecast\Forecast;

/**
 * Forecast-strategy for using local database cache
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Service\WeatherForecast\Strategy
 */
class DBWeatherCache implements StrategyInterface {
	/**
	 * Geohash Query Precision
	 * @var int
	 */
	const GEOHASH_QUERY_PRECISION = 4;

	/**
	 * PDO
	 * @var null|\PDO
	 */
	protected $PDO = null;

	/**
	 * WeatherCache
	 * @var null|\Runalyze\Model\WeatherCache\Entity $WeatherCache
	 */
	protected $WeatherCache = null;

	/**
	 * Location
	 * @var null|\Runalyze\Data\Weather\Location $Location
	 */
	protected $Location = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->WeatherCache = new WeatherCache\Entity([]);
	}

	/**
	 * @see \Runalyze\Data\Weather\Sources
	 * @return int
	 */
	public function sourceId()
	{
		return $this->WeatherCache->weatherSource();
	}

	/**
	 * Is it possible to receive weather data?
	 * @return boolean
	 */
	public function isPossible() {
	    return true;
	}

	/**
	 * Should this data be cached?
	 * @return boolean
	 */
	public function isCachable() {
	    return false;
	}

	/**
	 * @return boolean
	 */
	public function wasSuccessfull() {
		return !$this->WeatherCache->isEmpty();
	}

	/**
	 * Load conditions
	 * @param \Runalyze\Data\Weather\Location $Location
	 */
	public function loadForecast(Weather\Location $Location) {
	    $this->PDO = \DB::getInstance();
	    $this->Location = $Location;
	    $cacheData = [];

	    if ($this->Location->hasPosition()) {
	    	$qValues = array(
				'geohash' => substr($this->Location->geohash(), 0, self::GEOHASH_QUERY_PRECISION),
				'starttime' => $this->Location->timestamp() - Forecast::TIME_PRECISION,
				'endtime' => $this->Location->timestamp() + Forecast::TIME_PRECISION
			);

	    	$cacheData = $this->PDO->query('SELECT * FROM `'.PREFIX.'weathercache` WHERE `geohash` LIKE "'.$qValues['geohash'].'%" AND `time` BETWEEN "'.$qValues['starttime'].'" AND "'.$qValues['endtime'].'" ORDER BY TIME DESC LIMIT 1')->fetch();
	    }

		if (false === $cacheData) {
	    	$cacheData = [];
	    }

	    $this->WeatherCache = new WeatherCache\Entity($cacheData);
	}

	/**
	 * Condition
	 * @return \Runalyze\Data\Weather\Condition
	 */
	public function condition() {
		return new Weather\Condition($this->WeatherCache->weatherid());
	}

	/**
	 * Temperature
	 * @return \Runalyze\Data\Weather\Temperature
	 */
	public function temperature() {
		return new Weather\Temperature($this->WeatherCache->temperature());
	}

	/**
	 * WindSpeed
	 * @return \Runalyze\Data\Weather\WindSpeed
	 */
	public function windSpeed() {
		return new Weather\WindSpeed($this->WeatherCache->windSpeed());
	}

	/**
	 * WindDegree
	 * @return \Runalyze\Data\Weather\WindDegree
	 */
	public function windDegree() {
		return new Weather\WindDegree($this->WeatherCache->windDegree());
	}

	/**
	 * Humidity
	 * @return \Runalyze\Data\Weather\Humidity
	 */
	public function humidity() {
		return new Weather\Humidity($this->WeatherCache->humidity());
	}

	/**
	 * Pressure
	 * @return \Runalyze\Data\Weather\Pressure
	 */
	public function pressure() {
		return new Weather\Pressure($this->WeatherCache->pressure());
	}

	/**
	 * Location object
	 * @return null|\Runalyze\Data\Weather\Location
	 */
	public function location() {
	    return $this->Location;
	}
}
