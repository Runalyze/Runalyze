<?php
/**
 * This file contains interface::StrategyInterface
 * @package Runalyze\Service\WeatherForecast\Strategy
 */

namespace Runalyze\Service\WeatherForecast\Strategy;

use Runalyze\Data\Weather\Location;

/**
 * @deprecated since v4.3
 * @see StrategyInterface
 */
interface LegacyStrategyInterface
{
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
	 * Is it possible to receive weather data?
	 * @return boolean
	 */
	public function isPossible();

	/**
	 * Should this data be cached?
	 * @return boolean
	 */
	public function isCachable();

	/**
	 * @return boolean
	 */
	public function wasSuccessfull();

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

	/**
	 * Location object
	 * @return \Runalyze\Data\Weather\Location
	 */
	public function location();
}
