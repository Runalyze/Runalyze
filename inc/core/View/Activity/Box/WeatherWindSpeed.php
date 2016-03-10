<?php
/**
 * This file contains class::WeatherWindSpeed
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\View\Activity\Context;

/**
 * Boxed value for Wind Speed
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Box
 */
class WeatherWindSpeed extends ValueBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context)
	{
		parent::__construct(
			$Context->activity()->weather()->windSpeed()
		);
	}
}