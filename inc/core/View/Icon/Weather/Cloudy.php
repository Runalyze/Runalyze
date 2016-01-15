<?php
/**
 * This file contains class::Cloudy
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Cloudy
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Cloudy extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setBaseClass('weather-cloudy');
	}
}