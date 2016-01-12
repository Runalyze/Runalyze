<?php
/**
 * This file contains class::Thunderstorm
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Thunderstorm
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Thunderstorm extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setBaseClass('weather-basethundercloud');
		$this->setLayerClass('weather-thunder');
	}
}