<?php
/**
 * This file contains class::Forecast
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

/**
 * Weather forecast
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class Forecast {
	/**
	 * Strategy
	 * @var \Runalyze\Data\Weather\ForecastStrategyInterface
	 */
	protected $Strategy = null;

	/**
	 * Location
	 * @var Location 
	 */
	protected $Location = null;

	/**
	 * Constructor
	 * @param \Runalyze\Data\Weather\ForecastStrategyInterface $Strategy
	 * @param \Runalyze\Data\Weather\Location $Location
	 */
	public function __construct(ForecastStrategyInterface $Strategy, Location $Location) {
		$this->Strategy    = $Strategy;
		$this->Location    = $Location;

		$this->Strategy->loadForecast($this->Location);
	}

	/**
	 * Weather object
	 * @return \Runalyze\Data\Weather
	 */
	public function object() {
		return new \Runalyze\Data\Weather($this->Strategy->temperature(), $this->Strategy->condition());
	}
}