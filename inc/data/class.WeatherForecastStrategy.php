<?php
/**
 * This file contains interface::WeatherForecastStrategy
 * @package Runalyze\Data\Weather
 */
/**
 * Interface for forecasting wather
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
interface WeatherForecastStrategy {
	/**
	 * Load conditions
	 * @param WeatherLocation $Location
	 */
	public function loadForecast(WeatherLocation $Location);

	/**
	 * Get weather string
	 * @return string
	 */
	public function getConditionAsString();

	/**
	 * Get temperature
	 * @return mixed
	 */
	public function getTemperature();
}