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
	 * Time range for cache lookup (in seconds) (1 hours)
	 * @var int
	 */
	const TIME_PRECISION = 3600;
	
	/**
	 * Strategy
	 * @var \Runalyze\Data\Weather\Strategy\ForecastStrategyInterface
	 */
	protected $Strategy = null;

	/**
	 * Location
	 * @var \Runalyze\Data\Weather\Location 
	 */
	protected $Location = null;
	
	/**
	 * PDO
	 * @var \PDO 
	 */
	protected $PDO = null;
	
	/**
	 * Strategies
	 * @var array class names (absolute path) of available strategies
	 */
	protected $Strategies = [
		'\\Runalyze\\Data\\Weather\\Strategy\\DBWeatherCache',
		'\\Runalyze\\Data\\Weather\\Strategy\\Openweathermap'
	];

	/**
	 * Constructor
	 * @param \Runalyze\Data\Weather\Location $Location
	 * @param null|\Runalyze\Data\Weather\Strategy\ForecastStrategyInterface $StrategyToUse can be null to loop through all available strategies
	 */
	public function __construct(Location $Location, Strategy\ForecastStrategyInterface $StrategyToUse = null) {
		$this->Location = $Location;
		$this->PDO = \DB::getInstance();

		$this->adjustLocationTimeIfDateIsTodayAndTimeIsUnknown();
		$this->tryStrategies($StrategyToUse);
		$this->storeForecast();
	}

	/**
	 * @param null|\Runalyze\Data\Weather\Strategy\ForecastStrategyInterface $StrategyToUse can be null to loop through all available strategies
	 */
	protected function tryStrategies(Strategy\ForecastStrategyInterface $StrategyToUse = null) {
	    if ($StrategyToUse !== null) {
			$this->tryToLoadForecast($StrategyToUse);
	    } else {
			foreach ($this->Strategies as $strategyClassName) {
			    if ($this->tryToLoadForecast(new $strategyClassName)) {
					break;
			    }
			}
	    }
	}

	/**
	 * @param \Runalyze\Data\Weather\Strategy\ForecastStrategyInterface $Strategy
	 * @return bool flag if forecast could be loaded
	 */
	protected function tryToLoadForecast(Strategy\ForecastStrategyInterface $Strategy) {
		$this->Strategy = $Strategy;

		if ($this->Strategy->isPossible()) {
			$this->Strategy->loadForecast($this->Location);
	    }

	    return $this->Strategy->wasSuccessfull();
	}

	/**
	 * Store forecast
	 */
	protected function storeForecast() {
	    if (null !== $this->Strategy && $this->Strategy->isCachable() && null !== $this->Strategy->location() && $this->Strategy->location()->hasPosition()) {
			$Temperature = $this->Strategy->temperature();
			$Temperature->toCelsius();
			$Geohash = substr($this->Strategy->location()->geohash(), 0, WeatherCache\Entity::GEOHASH_PRECISION);
	
			if (!$this->locationIsAlreadyCached()) {
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
			    $Weather = new WeatherCache\Inserter($this->PDO, $WeatherCache);
			    $Weather->insert();
			}
	    }
	}

	/**
	 * Check if location is already in cache
	 * @return bool
	 */
	protected function locationIsAlreadyCached() {
		$qValues = array(
		    'geohash' => substr($this->Location->geohash(), 0, WeatherCache\Entity::GEOHASH_PRECISION),
		    'starttime' => $this->Location->time() - self::TIME_PRECISION,
		    'endtime' => $this->Location->time() + self::TIME_PRECISION
		);
		$rowCount = $this->PDO->query('SELECT 1 FROM `'.PREFIX.'weathercache` WHERE `geohash`="'.$qValues['geohash'].'" AND `time` BETWEEN "'.$qValues['starttime'].'" AND "'.$qValues['endtime'].'" LIMIT 1')->rowCount();

		return ($rowCount > 0);
	}

	/**
	 * Set time to now if date is today and time is unknown
	 */
	protected function adjustLocationTimeIfDateIsTodayAndTimeIsUnknown() {
	    if ($this->Location->hasTimestamp()) {
			if ($this->Location->time() == LocalTime::fromServerTime(time())->setTime(0, 0, 0)->getTimestamp()) {
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