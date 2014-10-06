<?php
/**
 * This file contains class::Fair
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Fair
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Fair extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setLayerClass('weather-cloudy weather-sunny');
	}
}