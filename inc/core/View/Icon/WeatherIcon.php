<?php
/**
 * This file contains class::WeatherIcon
 * @package Runalyze\View\Icon
 */

namespace Runalyze\View\Icon;

/**
 * Weather icon
 * 
 * @see http://forecastfont.iconvau.lt/
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon
 */
abstract class WeatherIcon extends \Runalyze\View\Icon {
	/**
	 * Base layer: cloud
	 * @var string
	 */
	const BASE_CLOUD = 'weather-basecloud';

	/**
	 * Base layer
	 * @var string
	 */
	protected $Base = '';

	/**
	 * Layer
	 * @var string
	 */
	protected $Layer = '';

	/**
	 * Weather Icon
	 * @param bool $isNight
	 */
	public function __construct($isNight = false) {
		parent::__construct('');
		
		$this->setLayer();
		
		if ($isNight == true) {
			$this->setAsNight();
		}
	}

	/**
	 * Set layer
	 */
	abstract protected function setLayer();

	/**
	 * Set base layer
	 * @param string $layer
	 */
	protected function setBaseClass($layer) {
		$this->Base = $layer;
	}

	/**
	 * Add layer
	 * @param string $layer
	 */
	protected function setLayerClass($layer) {
		$this->Layer = $layer;
	}

	/**
	 * Set weather icon as night
	 */
	public function setAsNight() {
		$this->Layer .= ' weather-night';
	}

	/**
	 * Display
	 */
	public function code() {
		$code = '<i class="weather '.$this->Base.'"'.$this->tooltipAttributes().'>';

		if (!empty($this->Layer)) {
			$code .= '<i class="'.$this->Layer.'"></i>';
		}

		$code .= '</i>';

		return $code;
	}
}