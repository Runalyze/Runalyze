<?php
/**
 * This file contains abstact::AbstractCacheableForecast
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

/**
 * Abstract for cacheable weather
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
abstract class AbstractCacheableForecast 
{
    
	/**
	 * Load forecast
	 * @return \Runalyze\Data\Weather\Forecast
	 */
	final function loadForecast() 
	{
	    $this->tryToLoadLocaleWeatherData();
	    $this->cacheForecast();


	}

	private function tryToLoadLocaleWeatherData()
	{
	    return false;
	}

	private function cacheForecast() 
	{
	    return false;
	}
}
