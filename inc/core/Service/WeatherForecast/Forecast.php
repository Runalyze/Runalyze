<?php
/**
 * This file contains class::Forecast
 * @package Runalyze\Service\WeatherForecast
 */

namespace Runalyze\Service\WeatherForecast;

use Runalyze\Data\Weather;
use Runalyze\Model\WeatherCache;
use Runalyze\Util\LocalTime;

/**
 * Weather forecast
 *
 * @author Hannes Christiansen
 * @package Runalyze\Service\WeatherForecast
 */
class Forecast {
	/**
	 * Time range for cache lookup (in seconds) (1 hours)
	 * @var int
	 */
	const TIME_PRECISION = 3600;

	/**
	 * Strategy
	 * @var \Runalyze\Service\WeatherForecast\Strategy\StrategyInterface
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
		'\\Runalyze\\Service\\WeatherForecast\\Strategy\\DBWeatherCache',
		'\\Runalyze\\Service\\WeatherForecast\\Strategy\\Openweathermap',
        '\\Runalyze\\Service\\WeatherForecast\\Strategy\\Darksky',
	];

	/**
	 * Constructor
	 * @param \Runalyze\Data\Weather\Location $Location
	 * @param null|\Runalyze\Service\WeatherForecast\Strategy\StrategyInterface $StrategyToUse can be null to loop through all available strategies
	 */
	public function __construct(Weather\Location $Location, Strategy\StrategyInterface $StrategyToUse = null) {
		$this->Location = $Location;
		$this->PDO = \DB::getInstance();

		$this->adjustLocationTimeIfDateIsTodayAndTimeIsUnknown();
		$this->tryStrategies($StrategyToUse);
		$this->storeForecast();
	}

	/**
	 * @param null|\Runalyze\Service\WeatherForecast\Strategy\StrategyInterface $StrategyToUse can be null to loop through all available strategies
	 */
	protected function tryStrategies(Strategy\StrategyInterface $StrategyToUse = null) {
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
	 * @param \Runalyze\Service\WeatherForecast\Strategy\StrategyInterface $Strategy
	 * @return bool flag if forecast could be loaded
	 */
	protected function tryToLoadForecast(Strategy\StrategyInterface $Strategy) {
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
			$WeatherObject = $this->object();

			if (!$this->locationIsAlreadyCached() && !$WeatherObject->isEmpty()) {
			    $WeatherCache = new WeatherCache\Entity([
					WeatherCache\Entity::TIME => $this->Location->time(),
					WeatherCache\Entity::GEOHASH => $Geohash,
					WeatherCache\Entity::TEMPERATURE => $Temperature->value(),
					WeatherCache\Entity::HUMIDITY => $WeatherObject->humidity()->value(),
					WeatherCache\Entity::PRESSURE => $WeatherObject->pressure()->value(),
					WeatherCache\Entity::WINDSPEED => $WeatherObject->windSpeed()->value(),
					WeatherCache\Entity::WINDDEG => $WeatherObject->windDegree()->value(),
					WeatherCache\Entity::WEATHERID => $WeatherObject->condition()->id(),
					WeatherCache\Entity::WEATHER_SOURCE => $WeatherObject->source()
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
	    if ($this->Location->hasDateTime()) {
			if ($this->Location->dateTime() == LocalTime::fromServerTime(time())->setTime(0, 0, 0)) {
			    $this->Location->setDateTime(new \DateTime());
			}
	    }
	}

	/**
	 * Weather object
	 * @return \Runalyze\Data\Weather
	 */
	public function object() {
		$weather = new Weather(
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
