<?php
/**
 * This file contains class::Sunny
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Sunny
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Sunny extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setBaseClass('weather-sun');
	}

	/**
	 * Set weather icon as night
	 */
	 public function setAsNight() {
	 	$this->setBaseClass('weather-moon');
	 }
}