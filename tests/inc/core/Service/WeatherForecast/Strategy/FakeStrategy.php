<?php
/**
 * This file contains class::FakeStrategy
 * @package Runalyze\Service\WeatherForecast\Strategy
 */

namespace Runalyze\Service\WeatherForecast\Strategy;

use Runalyze\Data\Weather;

/**
 * Interface for forecasting wather
 *
 * @author Hannes Christiansen
 * @package Runalyze\Service\WeatherForecast\Strategy
 */
class FakeStrategy implements StrategyInterface
{
	/** @var bool */
	protected $IsPossible = true;

	/** @var bool */
	protected $WasSuccessfull = true;

	/**
	 * @param boolean $isPossible
	 */
	public function __construct($isPossible = true, $wasSuccessfull = true)
	{
		$this->IsPossible = $isPossible;
		$this->WasSuccessfull = $wasSuccessfull;
	}

	/**
	 * @see \Runalyze\Data\Weather\Sources
	 * @return int
	 */
	public function sourceId()
	{
		return 1;
	}
	
	/**
	 * Is it possible to receive weather data?
	 * @return boolean
	 */
	public function isPossible()
	{
		return $this->IsPossible;
	}
	
	/**
	 * Should this data be cached?
	 * @return boolean
	 */
	public function isCachable()
	{
		return false;
	}

	/**
	 * @return boolean
	 */
	public function wasSuccessfull()
	{
		return $this->WasSuccessfull;
	}

	/**
	 * Load conditions
	 * @param \Runalyze\Data\Weather\Location $Location
	 */
	public function loadForecast(Weather\Location $Location)
	{
		// Nope, I don't want to do that!
	}

	/**
	 * Weather condition
	 * @return \Runalyze\Data\Weather\Condition
	 */
	public function condition()
	{
		return new Weather\Condition();
	}

	/**
	 * Temperature
	 * @return \Runalyze\Data\Weather\Temperature
	 */
	public function temperature()
	{
		return new Weather\Temperature();
	}
	
	/**
	 * Wind Speed
	 * @return \Runalyze\Data\Weather\WindSpeed
	 */
	public function windSpeed()
	{
		return new Weather\WindSpeed();
	}
	
	/**
	 * Wind degree
	 * @return \Runalyze\Data\Weather\WindDegree
	 */
	public function windDegree()
	{
		return new Weather\WindDegree();
	}
	
	/**
	 * Humidity
	 * @return \Runalyze\Data\Weather\Humidity
	 */
	public function humidity()
	{
		return new Weather\Humidity();
	}
	
	/**
	 * Pressure
	 * @return \Runalyze\Data\Weather\Pressure
	 */
	public function pressure()
	{
		return new Weather\Pressure();
	}
	
	/**
	 * Location object
	 * @return \Runalyze\Data\Weather\Location
	 */
	public function location()
	{
		return new Weather\Location();
	}
}
