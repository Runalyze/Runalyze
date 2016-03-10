<?php
/**
 * This file contains class::WeatherPressure
* @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\View\Activity\Context;

/**
 * Boxed value for Pressure
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Box
 */
class WeatherPressure extends ValueBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context)
	{
		parent::__construct(
			$Context->activity()->weather()->pressure()
		);
	}
}