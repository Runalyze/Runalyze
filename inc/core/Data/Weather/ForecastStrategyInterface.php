<?php
/**
 * This file contains interface::ForecastStrategyInterface
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

/**
 * Interface for forecasting wather
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
interface ForecastStrategyInterface {
	/**
	 * @see \Runalyze\Data\Weather\Sources
	 * @return int
	 */
	public function sourceId();

	/**
	 * Load conditions
	 * @param \Runalyze\Data\Weather\Location $Location
	 */
	public function loadForecast(Location $Location);

	/**
	 * Weather condition
	 * @return \Runalyze\Data\Weather\Condition
	 */
	public function condition();

	/**
	 * Temperature
	 * @return \Runalyze\Data\Weather\Temperature
	 */
	public function temperature();
	
	/**
	 * Wind Speed
	 * @return \Runalyze\Data\Weather\WindSpeed
	 */
	public function windSpeed();
	
	/**
	 * Wind degree
	 * @return \Runalyze\Data\Weather\WindDegree
	 */
	public function windDegree();
	
	/**
	 * Humidity
	 * @return \Runalyze\Data\Weather\Humidity
	 */
	public function humidity();
	
	/**
	 * Pressure
	 * @return \Runalyze\Data\Weather\Pressure
	 */
	public function pressure();
}