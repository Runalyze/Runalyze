<?php
/**
 * This file contains class::WeatherForecast
 * @package Runalyze\Data\Weather
 */
/**
 * Weather forecast
 *
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @package Runalyze\Data\Weather
 */
class WeatherForecast extends Weather {
	/**
	 * Forecast-Strategy
	 * @var \WeatherForecastStrategy
	 */
	protected $Strategy = null;

	/**
	 * Constructor
	 * @param \WeatherForecastStrategy $Strategy optional
	 */
	public function __construct(WeatherForecastStrategy $Strategy = null) {
		$this->id          = parent::$UNKNOWN_ID;
		$this->temperature = null;
		$this->Strategy    = (is_null($Strategy)) ? new WeatherOpenweathermap() : $Strategy;

		$this->loadForecast();
	}

	/**
	 * Load current conditions from API and set as internal data
	 */
	private function loadForecast() {
		$this->Strategy->loadForecast();

		$this->id          = $this->conditionToId($this->Strategy->getConditionAsString());
		$this->temperature = $this->Strategy->getTemperature();
	}
}