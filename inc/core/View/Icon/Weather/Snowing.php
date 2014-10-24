<?php
/**
 * This file contains class::Snowing
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Snowing
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Snowing extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setBaseClass('weather-basecloud');
		$this->setLayerClass('weather-snowy');
	}
}