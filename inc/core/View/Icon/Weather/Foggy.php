<?php
/**
 * This file contains class::Foggy
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Foggy
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Foggy extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setLayerClass('weather-mist');
	}

	/**
	 * Set weather icon as night
	 */
	public function setAsNight() {
		// Not possible
	}
}