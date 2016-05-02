<?php
/**
 * This file contains class::Forecast
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;
use Runalyze\Model\WeatherCache;
use Runalyze\Data\Weather\Strategy;
use Runalyze\Util\LocalTime;


/**
 * Weather forecast
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class Forecast {
	/**
	 * Time range for cache lookup (in seconds) (2 hours)
	 * @var int
	 */
	const TIME_PRECISION = 7200;
	
	/**
	 * Strategy
	 * @var \Runalyze\Data\Weather\Strategy\ForecastStrategyInterface
	 */
	protected $Strategy = null;

	/**
	 * Location
	 * @var Location 
	 */
	protected $Location = null;
	
	/**
	 * PDO
	 * @var PDO 
	 */
	protected $Pdo = null;
	
	/**
	 * Strategies
	 * @array Strategies
	 */
	protected $Strategies = array('DBWeatherCache', 'Openweathermap');

	/**
	 * Constructor
	 * @param \Runalyze\Data\Weather\Location $Location
	 * @param \Runalyze\Data\Weather\Strategy\ForecastStrategyInterface $UseStrategy
	 */
	public function __construct(Location $Location, Strategy\ForecastStrategyInterface $UseStrategy = null) {
		$this->Location    = $Location;
		$this->Pdo	   = \DB::getInstance();
		$this->checkLocationTime();
		$this->tryStrategies($UseStrategy);
		$this->storeForecast();

	}
	
	protected function tryStrategies($UseStrategy = null) {
	    if($UseStrategy !== null) {
		$this->tryToLoadForecast($UseStrategy);
	    } else {
		foreach($this->Strategies as $Strategy) {
		    $Strategy = '\\Runalyze\Data\Weather\Strategy\\'.$Strategy;
		    $Strategy = new $Strategy;
		    if ($this->tryToLoadForecast($Strategy)) {
			//TODO Not correct to stop Loop
			break;
		    }
		    
		}			    
	    }
	}
	
	protected function tryToLoadForecast($Strategy) {
	    $this->Strategy = $Strategy;
	    if ($this->Strategy->isPossible()) {
		$this->Strategy->loadForecast($this->Location);
	    }
	    //TODO Check is wrong
	    if ($this->Strategy->temperature()->value() !== NULL) {
		return true;
	    } else {
		return false;
	    }
	}
	
	protected function storeForecast() {
	    if ($this->Strategy->location()->hasPosition() && $this->Strategy->isCachable()) {
		$Temperature = $this->Strategy->temperature();
		$Temperature->toCelsius();
		$Geohash = substr($this->Location->geohash(), 0, WeatherCache\Entity::GEOHASH_PRECISION);

		if(!($this->checkCache())) {
		    $WeatherCache = new WeatherCache\Entity([
			WeatherCache\Entity::TIME => $this->Location->time(),
			WeatherCache\Entity::GEOHASH => $Geohash,
			WeatherCache\Entity::TEMPERATURE => $Temperature->value(),
			WeatherCache\Entity::HUMIDITY => $this->Strategy->humidity()->value(),
			WeatherCache\Entity::PRESSURE => $this->Strategy->pressure()->value(),
			WeatherCache\Entity::WINDSPEED => $this->Strategy->windSpeed()->value(),
			WeatherCache\Entity::WINDDEG => $this->Strategy->windDegree()->value(),
			WeatherCache\Entity::WEATHERID => $this->Strategy->condition()->id(),
			WeatherCache\Entity::WEATHER_SOURCE => $this->Strategy->sourceId()
		    ]);
		    $Weather = new WeatherCache\Inserter($this->Pdo, $WeatherCache);
		    $Weather->insert();
		}
	    }
	}
	
	protected function checkCache() {
	    $Geohash = substr($this->Location->geohash(), 0, WeatherCache\Entity::GEOHASH_PRECISION);
		$bindValues = array(
		    'geohash' => $Geohash,
		    'starttime' => $this->Location->time() - self::TIME_PRECISION,
		    'endtime' => $this->Location->time() + self::TIME_PRECISION
		);
		    
		$data = $this->Pdo->prepare('SELECT 1 FROM '.PREFIX.'weathercache WHERE `geohash`=:geohash AND `time` BETWEEN :starttime AND :endtime ORDER BY TIME DESC LIMIT 1');
		$data->execute($bindValues);
		return ($data->rowCount() > 0) ? true : false;
	}


	protected function checkLocationTime() {
	    if($this->Location->hasTimestamp()) {
		if($this->Location->time() == LocalTime::fromServerTime(time())->setTime(0, 0, 0)->getTimestamp()) {
		    $this->Location->setTimestamp(time());
		}
	    }
	}

	/**
	 * Weather object
	 * @return \Runalyze\Data\Weather
	 */
	public function object() {
		$weather = new \Runalyze\Data\Weather(
			$this->Strategy->temperature(),
			$this->Strategy->condition(),
			$this->Strategy->windSpeed(),
			$this->Strategy->windDegree(),
			$this->Strategy->humidity(),
			$this->Strategy->pressure()
		);

		if (!$weather->isEmpty()) {
			$weather->setSource($this->Strategy->sourceId());
		}

		return $weather;
	}
}