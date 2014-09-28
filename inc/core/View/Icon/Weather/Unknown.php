<?php
/**
 * This file contains class::Unknown
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Unknown
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\„eather
 */
class Unknown extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		
	}

	/**
	 * Code
	 * @return string
	 */
	public function code() {
		return '<i class="weather"></i>';
	}
}