<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\WeatherCache
 */

namespace Runalyze\Model\WeatherCache;

use Runalyze\Data\Weather;
use Runalyze\Model;

/**
 * WeatherCache entity
 * 
 * @author Hannes Christiansen 
 * @author Michael Pohl
 * @package Runalyze\Model\WeatherCache
 */
class Entity extends Model\Entity {
    
	/**
	 * Key: geohash precision
	 * @var int
	 */
	const GEOHASH_PRECISION = 5;
    
	/**
	 * Key: time
	 * @var string
	 */
	const TIME = 'time';   
	
	/**
	 * Key: geohash
	 * @var string
	 */
	const GEOHASH = 'geohash';
	
	/**
	 * Key: temperature
	 * @var string
	 */
	const TEMPERATURE = 'temperature';
	
	/**
	 * Key: wind speed
	 * @var string
	 */
	const WINDSPEED = 'wind_speed';
	
	/**
	 * Key: wind degree
	 * @var string
	 */
	const WINDDEG = 'wind_deg';
	
	/**
	 * Key: humidity
	 * @var string
	 */
	const HUMIDITY = 'humidity';
	
	/**
	 * Key: pressure
	 * @var string
	 */
	const PRESSURE = 'pressure';

	/**
	 * Key: weather id
	 * @var string
	 */
	const WEATHERID = 'weatherid';

	/**
	 * Key: weather source
	 * @var string
	 */
	const WEATHER_SOURCE = 'weather_source';

	/**
	 * All properties
	 * @return array
	 */
	static public function allDatabaseProperties() {
		return array(
			self::TIME,
			self::GEOHASH,
			self::TEMPERATURE,
			self::WINDSPEED,
			self::WINDDEG,
			self::HUMIDITY,
			self::PRESSURE,
			self::WEATHERID,
			self::WEATHER_SOURCE
		);
	}
	
	/**
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
		switch ($key) {
			case self::TEMPERATURE:
			case self::WINDSPEED:
			case self::WINDDEG:
			case self::HUMIDITY:
			case self::PRESSURE:
			case self::WEATHER_SOURCE:
				return true;
		}

		return false;
	}
	
	/**
	 * Properties
	 * @return array
	 */
	public function properties() {
		return static::allDatabaseProperties();
	}

	/**
	 * Time
	 * @return int
	 */
	public function time() {
		return $this->Data[self::TIME];
	}
	
	/**
	 * Geohash
	 * @return string
	 */
	public function geohash() {
		return $this->Data[self::GEOHASH];
	}
	
	/**
	 * Temperature
	 * @return float
	 */
	public function temperature() {
		return $this->Data[self::TEMPERATURE];
	}
	
	/**
	 * WindSpeed
	 * @return int
	 */
	public function windSpeed() {
		return $this->Data[self::WINDSPEED];
	}
	
	/**
	 * WindDegree
	 * @return int
	 */
	public function windDegree() {
		return $this->Data[self::WINDDEG];
	}
	
	/**
	 * Humidity
	 * @return int
	 */
	public function humidity() {
		return $this->Data[self::HUMIDITY];
	}
	
	/**
	 * Pressure
	 * @return int
	 */
	public function pressure() {
		return $this->Data[self::PRESSURE];
	}
	
	/**
	 * Weather id
	 * @return int
	 */
	public function weatherid() {
		return $this->Data[self::WEATHERID];
	}
	
	/**
	 * Weather source
	 * @return null|int
	 */
	public function weatherSource() {
		return $this->Data[self::WEATHER_SOURCE];
	}
	
	
	/**
	 * Weather
	 * @return \Runalyze\Data\Weather
	 */
	public function weather() {
		if (is_null($this->Weather)) {
			$this->Weather = new Weather(
				new Weather\Temperature($this->Data[self::TEMPERATURE]),
				new Weather\Condition($this->Data[self::WEATHERID]),
				new Weather\WindSpeed($this->Data[self::WINDSPEED]),
				new Weather\WindDegree($this->Data[self::WINDDEG]),
				new Weather\Humidity($this->Data[self::HUMIDITY]),
				new Weather\Pressure($this->Data[self::PRESSURE])
			);
			$this->Weather->setSource($this->Data[self::WEATHER_SOURCE]);
		}

		return $this->Weather;
	}

}
