<?php
/**
 * This file contains class::WeatherHumidity
* @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

/**
 * Boxed value for Humidity
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Box
 */
class WeatherHumidity extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$humidity = $Context->activity()->weather()->humidity();
		parent::__construct(
			\Helper::Unknown($humidity->string(false), '-'),
			$humidity->unit(),
			$humidity->label()
		);
	}
}