<?php
/**
 * This file contains class::WeatherWindSpeed
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

/**
 * Boxed value for Wind Speed
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Box
 */
class WeatherWindSpeed extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$WindSpeed = $Context->activity()->weather()->windSpeed();
		parent::__construct(
			\Helper::Unknown($WindSpeed->string(false), '-'),
			$WindSpeed->unit(),
			$WindSpeed->label()
		);
	}
}