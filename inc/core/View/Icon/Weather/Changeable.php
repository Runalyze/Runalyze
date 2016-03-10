<?php
/**
 * This file contains class::Changeable
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Changeable
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Changeable extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setBaseClass('weather-basecloud');
		$this->setLayerClass('weather-drizzle weather-sunny');
	}

	/**
	 * Set weather icon as night
	 */
	public function setAsNight() {
		$this->Layer = str_replace('weather-sunny', 'weather-night', $this->Layer);
	}
}