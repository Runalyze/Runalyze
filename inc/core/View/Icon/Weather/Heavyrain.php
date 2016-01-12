<?php
/**
 * This file contains class::Heavyrain
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Heavyrain
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Heavyrain extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setBaseClass('weather-basecloud');
		$this->setLayerClass('weather-showers');
	}
}