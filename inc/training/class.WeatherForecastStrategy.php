<?php
/**
 * This file contains interface::WeatherForecastStrategy
 * @package Runalyze\Data\Weather
 */
/**
 * Interface for forecasting wather
 *
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @package Runalyze\Data\Weather
 */
interface WeatherForecastStrategy {
	/**
	 * Load conditions
	 */
	public function loadForecast();

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