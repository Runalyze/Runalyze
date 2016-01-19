<?php
/**
 * This file contains class::Rainy
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Rainy
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Rainy extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setBaseClass('weather-basecloud');
		$this->setLayerClass('weather-drizzle');
	}
}