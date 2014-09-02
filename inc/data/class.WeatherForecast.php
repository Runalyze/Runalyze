<?php
/**
 * This file contains class::WeatherForecast
 * @package Runalyze\Data\Weather
 */
/**
 * Weather forecast
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class WeatherForecast extends Weather {
	/**
	 * Forecast-Strategy
	 * @var \WeatherForecastStrategy
	 */
	protected $Strategy = null;

	/**
	 * Location
	 * @var \WeatherLocation 
	 */
	protected $Location = null;

	/**
	 * Constructor
	 * @param \WeatherForecastStrategy $Strategy
	 * @param \WeatherLocation $Location
	 */
	public function __construct(WeatherForecastStrategy $Strategy, WeatherLocation $Location) {
		$this->id          = parent::$UNKNOWN_ID;
		$this->temperature = null;
		$this->Strategy    = $Strategy;
		$this->Location    = $Location;

		$this->loadForecast();
	}

	/**
	 * Load current conditions from API and set as internal data
	 */
	private function loadForecast() {
		$this->Strategy->loadForecast($this->Location);

		$this->id          = self::conditionToId($this->Strategy->getConditionAsString());
		$this->temperature = $this->Strategy->getTemperature();
	}
}