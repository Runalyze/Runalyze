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
	 * Load conditions
	 * @param \Runalyze\Data\Weather\Location $Location
	 */
	public function loadForecast(Location $Location);

	/**
	 * Weather condition
	 * @return \Runalze\Data\Weather\Condition
	 */
	public function condition();

	/**
	 * Temperature
	 * @return \Runalze\Data\Weather\Temperature
	 */
	public function temperature();
}