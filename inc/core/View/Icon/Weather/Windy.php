<?php
/**
 * This file contains class::Windy
 * @package Runalyze\View\Icon\Weather
 */

namespace Runalyze\View\Icon\Weather;

/**
 * Weather icon: Windy
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon\Weather
 */
class Windy extends \Runalyze\View\Icon\WeatherIcon {
	/**
	 * Display
	 */
	protected function setLayer() {
		$this->setLayerClass('weather-windy');
	}

    /**
     * Set weather icon as night
     */
    public function setAsNight() {
        // Not possible
    }
}
