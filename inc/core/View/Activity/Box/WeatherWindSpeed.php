<?php
/**
 * This file contains class::WeatherWindSpeed
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

/**
 * Boxed value for elevation
 * 
 * @author Hannes Christiansen
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
			\Helper::Unknown($WindSpeed->value(), '-'),
			$WindSpeed->unit(),
			$WindSpeed->label()
		);
	}
}